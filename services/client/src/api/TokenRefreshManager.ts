import type { Cookies } from '@sveltejs/kit';

import { Auth } from '@slink/lib/auth/Auth';
import type { CookieManager } from '@slink/lib/auth/CookieManager';
import type { TokenPair } from '@slink/lib/auth/Type/TokenPair';

import { TokenRefreshError, handleApiError } from './ApiError';

type RefreshContext = {
  cookies: Cookies;
  cookieManager: CookieManager;
  fetch: typeof fetch;
};

type QueuedRequest = {
  resolve: (response: Response) => void;
  reject: (error: Error) => void;
  makeRequest: (accessToken?: string) => Promise<Response>;
};

export type TokenRefreshResult = {
  response: Response;
  tokensRefreshed: boolean;
};

export class TokenRefreshManager {
  private static instance: TokenRefreshManager;

  private refreshPromises = new Map<string, Promise<TokenPair>>();
  private requestQueues = new Map<string, QueuedRequest[]>();

  private constructor() {}

  public static getInstance(): TokenRefreshManager {
    if (!this.instance) {
      this.instance = new TokenRefreshManager();
    }
    return this.instance;
  }

  public async handleTokenRefresh(
    sessionId: string,
    context: RefreshContext,
    makeRequest: (accessToken?: string) => Promise<Response>,
  ): Promise<TokenRefreshResult> {
    const isInitiatorRequest = !this.refreshPromises.has(sessionId);

    if (isInitiatorRequest) {
      this.startRefresh(sessionId, context);
    }

    const response = await this.enqueue(sessionId, makeRequest);

    return { response, tokensRefreshed: isInitiatorRequest };
  }

  private startRefresh(sessionId: string, context: RefreshContext): void {
    const refreshPromise = Auth.refresh(context).then((tokens) => {
      if (!tokens) {
        throw new TokenRefreshError('Failed to refresh access token');
      }
      return tokens;
    });

    this.refreshPromises.set(sessionId, refreshPromise);

    refreshPromise
      .then((tokens) => this.processQueue(sessionId, true, tokens.accessToken))
      .catch((error) => {
        this.processQueue(sessionId, false, undefined, handleApiError(error));
      })
      .finally(() => {
        this.refreshPromises.delete(sessionId);
        this.requestQueues.delete(sessionId);
      });
  }

  private enqueue(
    sessionId: string,
    makeRequest: (accessToken?: string) => Promise<Response>,
  ): Promise<Response> {
    return new Promise((resolve, reject) => {
      if (!this.requestQueues.has(sessionId)) {
        this.requestQueues.set(sessionId, []);
      }
      this.requestQueues.get(sessionId)!.push({ resolve, reject, makeRequest });
    });
  }

  private async processQueue(
    sessionId: string,
    success: boolean,
    accessToken?: string,
    error?: Error,
  ): Promise<void> {
    const queue = this.requestQueues.get(sessionId) || [];

    if (!success || !accessToken) {
      for (const queuedRequest of queue) {
        queuedRequest.reject(
          error || new TokenRefreshError('Token refresh failed'),
        );
      }
      return;
    }

    const results = await Promise.allSettled(
      queue.map((queuedRequest) => queuedRequest.makeRequest(accessToken)),
    );

    results.forEach((result, index) => {
      if (result.status === 'fulfilled') {
        queue[index].resolve(result.value);
      } else {
        queue[index].reject(result.reason as Error);
      }
    });
  }

  public cleanup(): void {
    this.refreshPromises.clear();
    this.requestQueues.clear();
  }
}
