import type { User } from '@slink/lib/auth/Type/User';
import type { Cookies } from '@sveltejs/kit';

import { browser } from '$app/environment';
import { type Writable, get, writable } from 'svelte/store';

type SessionItem<T> = {
  data: Writable<T>;
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

  private _store: Record<string, SessionItem<SessionData>> = {};

  private constructor() {}

  public create(cookies: Cookies, ttl: number = 0): string {
    const sessionId = crypto.randomUUID();

    if (cookies.get('sessionId')) {
      return cookies.get('sessionId') as string;
    }

    const data = writable({
      user: null,
    });

    this._store[sessionId] = {
      data,
      expires: ttl ? Date.now() + ttl : 0,
    };

    cookies.set('sessionId', sessionId, {
      sameSite: 'strict',
      path: '/',
      secure: true,
    });

    return sessionId;
  }

  public get(sessionId: string | undefined): SessionData | undefined {
    if (!sessionId) return;

    const session = this._store[sessionId];

    if (!session) return;

    if (session.expires && session.expires < Date.now()) {
      delete this._store[sessionId];
      return;
    }

    return get(session.data);
  }

  public set(sessionId: string | undefined, data: Partial<SessionData>) {
    if (!sessionId) return;

    const session = this._store[sessionId];

    if (!session) return;

    session.data.update((current) => ({ ...current, ...data }));
  }

  public destroy(cookies: Cookies) {
    const sessionId = cookies.get('sessionId');

    if (!sessionId) return;

    cookies.delete('sessionId', {
      sameSite: 'strict',
      path: '/',
      secure: true,
    });

    delete this._store[sessionId];
  }
}

export const Session = SessionManager.instance;
