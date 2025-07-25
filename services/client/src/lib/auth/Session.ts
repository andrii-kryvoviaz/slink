import type { Cookies } from '@sveltejs/kit';

import { browser } from '$app/environment';

import { RedisSessionProvider } from '@slink/lib/auth/SessionProvider/RedisSessionProvider';
import type { SessionProviderInterface } from '@slink/lib/auth/SessionProvider/SessionProviderInterface';
import type { User } from '@slink/lib/auth/Type/User';

type SessionItem = {
  data: SessionData;
  expires: number;
};

type SessionData = {
  user: User | null;
} & Record<string, any>;

class SessionManager {
  private static _instance: SessionManager;

  public static get instance() {
    if (browser)
      throw new Error('SessionManager is not available in the browser');

    return this._instance ?? (this._instance = new SessionManager());
  }

  private _provider: SessionProviderInterface = null as any;

  public setProvider(provider: SessionProviderInterface) {
    this._provider = provider;

    return this;
  }

  public async create(
    cookies: Cookies,
    ttl: number | null = null,
  ): Promise<string> {
    const existingSessionId = cookies.get('sessionId');

    if (existingSessionId) {
      const session = await this._provider.get<SessionItem>(existingSessionId);

      if (session) {
        return existingSessionId;
      } else {
        await this.destroy(cookies);
      }
    }

    const sessionId = crypto.randomUUID();

    await this._provider.create(sessionId, ttl);

    cookies.set('sessionId', sessionId, {
      sameSite: 'strict',
      path: '/',
    });

    return sessionId;
  }

  public async get(
    sessionId: string | undefined,
  ): Promise<SessionData | undefined> {
    if (!sessionId) return;

    const session = await this._provider.get<SessionItem>(sessionId);

    if (!session) return;

    if (session && session.expires && session.expires < Date.now()) {
      await this._provider.destroy(sessionId);
      return;
    }

    return session.data;
  }

  public async set(
    sessionId: string | undefined,
    data: Partial<SessionData>,
    ttl: number | null = null,
  ) {
    if (!sessionId) return;

    const session = await this._provider.get<SessionItem>(sessionId);

    if (!session) return;

    session.data = { ...session.data, ...data };

    await this._provider.set(sessionId, session, ttl);
  }

  public async destroy(cookies: Cookies) {
    const sessionId = cookies.get('sessionId');

    if (!sessionId) return;

    cookies.delete('sessionId', {
      sameSite: 'strict',
      path: '/',
    });

    await this._provider.destroy(sessionId);
  }
}

const provider = await RedisSessionProvider.createClient();

export const Session = SessionManager.instance.setProvider(provider);
