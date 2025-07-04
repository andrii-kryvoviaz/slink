import type { Cookies } from '@sveltejs/kit';

import { Auth } from '@slink/lib/auth/Auth';

import { TokenRefreshError, handleApiError } from './ApiError';

type RefreshContext = {
  cookies: Cookies;
  fetch: typeof fetch;
};

type QueuedRequest = {
  resolve: (response: Response) => void;
  reject: (error: Error) => void;
  makeRequest: (accessToken?: string) => Promise<Response>;
};

type TokenPair = {
  accessToken: string;
  refreshToken: string;
};

export class TokenRefreshManager {
  private static instance: TokenRefreshManager;

  private refreshPromises = new Map<string, Promise<TokenPair | undefined>>();
  private requestQueues = new Map<string, QueuedRequest[]>();
  private isRefreshing = new Set<string>();

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
  ): Promise<Response> {
    if (this.isRefreshing.has(sessionId)) {
      return this.queueRequest(sessionId, makeRequest);
    }

    this.isRefreshing.add(sessionId);

    try {
      const tokens = await this.refreshTokens(sessionId, context);

      if (!tokens) {
        throw new TokenRefreshError('Failed to refresh access token');
      }

      const response = await makeRequest(tokens.accessToken);

      await this.processQueue(sessionId, true, tokens.accessToken);

      return response;
    } catch (error) {
      const apiError = handleApiError(error);
      this.processQueue(sessionId, false, undefined, apiError);
      throw apiError;
    } finally {
      this.isRefreshing.delete(sessionId);
      this.refreshPromises.delete(sessionId);
      this.requestQueues.delete(sessionId);
    }
  }

  private async refreshTokens(
    sessionId: string,
    context: RefreshContext,
  ): Promise<TokenPair | undefined> {
    if (this.refreshPromises.has(sessionId)) {
      return this.refreshPromises.get(sessionId)!;
    }

    const refreshPromise = Auth.refresh(context);
    this.refreshPromises.set(sessionId, refreshPromise);

    return refreshPromise;
  }

  private queueRequest(
    sessionId: string,
    makeRequest: (accessToken?: string) => Promise<Response>,
  ): Promise<Response> {
    return new Promise((resolve, reject) => {
      if (!this.requestQueues.has(sessionId)) {
        this.requestQueues.set(sessionId, []);
      }

      this.requestQueues.get(sessionId)!.push({
        resolve,
        reject,
        makeRequest,
      });
    });
  }

  private async processQueue(
    sessionId: string,
    success: boolean,
    accessToken?: string,
    error?: Error,
  ): Promise<void> {
    const queue = this.requestQueues.get(sessionId) || [];

    for (const queuedRequest of queue) {
      if (success && accessToken) {
        try {
          const response = await queuedRequest.makeRequest(accessToken);
          queuedRequest.resolve(response);
        } catch (err) {
          queuedRequest.reject(err as Error);
        }
      } else {
        queuedRequest.reject(
          error || new TokenRefreshError('Token refresh failed'),
        );
      }
    }
  }

  public cleanup(): void {
    this.refreshPromises.clear();
    this.requestQueues.clear();
    this.isRefreshing.clear();
  }
}
