import { ListenerAware } from '@slink/lib/listener';
import type { Cookies, Handle } from '@sveltejs/kit';

import { browser } from '$app/environment';

interface CookieProvider {
  get: (key: string, defaultValue?: string) => string;
  set: (key: string, value: string, ttl?: number) => void;
  remove: (key: string) => void;
}

class BrowserCookieProvider implements CookieProvider {
  get(key: string, defaultValue: string = ''): string {
    const match = document.cookie.match(new RegExp('(^| )' + key + '=([^;]+)'));
    return match ? match[2] : defaultValue;
  }

  set(key: string, value: string, ttl?: number): void {
    if (!ttl) {
      document.cookie = `${key}=${value};path=/;Secure;SameSite=Strict`;
      return;
    }

    const date = new Date();
    date.setTime(date.getTime() + ttl * 1000);
    document.cookie = `${key}=${value};expires=${date.toUTCString()};path=/;Secure;SameSite=Strict`;
  }

  remove(key: string): void {
    document.cookie = `${key}=;expires=Thu, 01 Jan 1970 00:00:00 GMT;`;
  }
}

class Cookie extends ListenerAware {
  private _provider: CookieProvider = new BrowserCookieProvider();
  private _serverCookies: Cookies | null = null;

  setServerCookies(cookies: Cookies) {
    this._serverCookies = cookies;
  }

  private _warningMessage() {
    console.warn(
      `You are currently running in a server environment. Use SvelteKit's cookies to manage cookies on the server.`
    );
  }

  get(key: string, defaultValue?: string): string | undefined {
    if (!browser) {
      // allow server-side read only
      // helps to prerender e.g. the theme
      return this._serverCookies?.get(key) || defaultValue;
    }

    return this._provider.get(key, defaultValue);
  }

  set(key: string, value: string, ttl?: number): void {
    if (!browser) {
      this._warningMessage();
      return;
    }

    this._provider.set(key, value, ttl);
    this._listener.notify(key, value);
  }

  remove(key: string): void {
    if (!browser) {
      this._warningMessage();
      return;
    }

    this._provider.remove(key);
  }
}

export const cookie = new Cookie();
export const setServerCookiesHandle: Handle = async ({ event, resolve }) => {
  cookie.setServerCookies(event.cookies);
  return resolve(event);
};
