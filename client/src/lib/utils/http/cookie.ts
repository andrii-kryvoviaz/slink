import type { Cookies } from '@sveltejs/kit';

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

class Cookie {
  private _provider: CookieProvider = new BrowserCookieProvider();

  private _warningMessage() {
    console.warn(
      `You are currently running in a server environment. Use SvelteKit's cookies to manage cookies on the server.`
    );
  }

  get(key: string, defaultValue?: string): string | undefined {
    if (!browser) {
      return defaultValue;
    }

    return this._provider.get(key, defaultValue);
  }

  set(key: string, value: string, ttl?: number): void {
    if (!browser) {
      this._warningMessage();
      return;
    }

    this._provider.set(key, value, ttl);
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

export const getResponseWithCookies = (
  response: Response,
  cookies: Cookies
): Response => {
  const { body, status } = response;
  const headers = new Headers(response.headers);

  cookies.getAll().forEach(({ name, value }) => {
    if (!name.startsWith('settings.')) {
      let cookieString = `${name}=${value}; Path=/; HttpOnly; Secure; SameSite=Strict;`;

      if (!value) {
        cookieString = `${cookieString} Max-Age=0;`;
      }

      headers.append('Set-Cookie', cookieString);
    }
  });

  return new Response(body, {
    status,
    headers,
  });
};
