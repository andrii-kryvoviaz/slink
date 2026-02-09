import type { Cookies } from '@sveltejs/kit';

import { browser } from '$app/environment';

interface CookieProvider {
  get: (key: string, defaultValue?: string) => string;
  set: (key: string, value: string, ttl?: number) => void | Promise<void>;
  remove: (key: string) => void;
}

class BrowserCookieProvider implements CookieProvider {
  constructor(private requireSsl: boolean = false) {}

  get(key: string, defaultValue: string = ''): string {
    const match = document.cookie.match(new RegExp('(^| )' + key + '=([^;]+)'));
    return match ? match[2] : defaultValue;
  }

  async set(key: string, value: string, ttl?: number): Promise<void> {
    const secureFlag = this.requireSsl ? ';Secure' : '';

    if (!ttl) {
      document.cookie = `${key}=${value};path=/${secureFlag};SameSite=Strict`;
      return;
    }

    const date = new Date();
    date.setTime(date.getTime() + ttl * 1000);
    document.cookie = `${key}=${value};expires=${date.toUTCString()};path=/${secureFlag};SameSite=Strict`;
  }

  remove(key: string): void {
    document.cookie = `${key}=;expires=Thu, 01 Jan 1970 00:00:00 GMT;`;
  }
}

class Cookie {
  private _provider: CookieProvider;

  constructor(requireSsl: boolean = false) {
    this._provider = new BrowserCookieProvider(requireSsl);
  }

  private _warningMessage() {
    console.warn(
      `You are currently running in a server environment. Use SvelteKit's cookies to manage cookies on the server.`,
    );
  }

  get(key: string, defaultValue?: string): string | undefined {
    if (!browser) {
      return defaultValue;
    }

    return this._provider.get(key, defaultValue);
  }

  async set(key: string, value: string, ttl?: number): Promise<void> {
    if (!browser) {
      this._warningMessage();
      return;
    }

    await this._provider.set(key, value, ttl);
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

type ResponseWithCookiesData = {
  response: Response;
  cookies: Cookies;
  requireSsl?: boolean;
  authRefreshed?: boolean;
};

export const getResponseWithCookies = async ({
  response,
  cookies,
  requireSsl = false,
  authRefreshed,
}: ResponseWithCookiesData): Promise<Response> => {
  const { body, status } = response;
  const headers = new Headers(response.headers);

  const AUTH_COOKIE_NAMES = new Set(['refreshToken', 'sessionId']);

  cookies.getAll().forEach(({ name, value }) => {
    if (name.startsWith('settings.')) return;
    if (!authRefreshed && AUTH_COOKIE_NAMES.has(name)) return;

    let cookieString = `${name}=${value}; Path=/; HttpOnly; SameSite=Strict;`;

    if (requireSsl) {
      cookieString += ' Secure;';
    }

    if (!value) {
      cookieString = `${cookieString} Max-Age=0;`;
    }

    headers.append('Set-Cookie', cookieString);
  });

  if (authRefreshed) {
    headers.append('x-auth-refreshed', 'true');
  }

  return new Response(body, {
    status,
    headers,
  });
};
