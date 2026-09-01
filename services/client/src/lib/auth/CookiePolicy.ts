import type { Cookies } from '@sveltejs/kit';

import type {
  CookieManager,
  CookieOptions,
} from '@slink/lib/auth/CookieManager';

export type CookiePolicy<K extends string = string> = {
  keys: readonly K[];
  name(key: K): string;
  encode(value: unknown): string;
  options: CookieOptions;
};

export class ScopedCookies<K extends string> {
  constructor(
    private _cookieManager: CookieManager,
    private _cookies: Cookies,
    private _policy: CookiePolicy<K>,
  ) {}

  public set(key: K, value: unknown): void {
    const name = this._policy.name(key);
    const cookieValue = this._policy.encode(value);

    if (this._cookies.get(name) === cookieValue) {
      return;
    }

    this._cookieManager.setCookie(
      this._cookies,
      name,
      cookieValue,
      this._policy.options,
    );
  }

  public clear(): void {
    for (const key of this._policy.keys) {
      this._cookieManager.deleteCookie(this._cookies, this._policy.name(key));
    }
  }
}

export type CookieScopes<P extends Record<string, CookiePolicy<string>>> = {
  [N in keyof P]: P[N] extends CookiePolicy<infer K> ? ScopedCookies<K> : never;
};

export type ScopedCookieManager<
  P extends Record<string, CookiePolicy<string>>,
> = CookieManager & CookieScopes<P>;

export const withScopes = <P extends Record<string, CookiePolicy<string>>>(
  cookieManager: CookieManager,
  policies: P,
): ScopedCookieManager<P> => {
  for (const name of Object.keys(policies)) {
    let scope: ScopedCookies<string> | undefined;

    Object.defineProperty(cookieManager, name, {
      get: () => (scope ??= cookieManager.use(policies[name])),
    });
  }

  return cookieManager as ScopedCookieManager<P>;
};
