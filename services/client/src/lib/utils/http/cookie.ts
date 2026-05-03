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

const AUTH_COOKIE_NAMES = ['refreshToken', 'sessionId'] as const;

export const getResponseWithCookies = async ({
  response,
  cookies,
  requireSsl = false,
  authRefreshed,
}: ResponseWithCookiesData): Promise<Response> => {
  if (!authRefreshed) {
    return response;
  }

  const headers = new Headers(response.headers);

  for (const name of AUTH_COOKIE_NAMES) {
    const value = cookies.get(name);

    let cookieString: string;
    if (!value) {
      cookieString = `${name}=; Path=/; HttpOnly; SameSite=Strict; Max-Age=0;`;
    } else {
      cookieString = `${name}=${encodeURIComponent(value)}; Path=/; HttpOnly; SameSite=Strict;`;
    }

    if (requireSsl) {
      cookieString += ' Secure;';
    }

    headers.append('Set-Cookie', cookieString);
  }

  headers.append('x-auth-refreshed', 'true');

  return new Response(response.body, {
    status: response.status,
    headers,
  });
};
