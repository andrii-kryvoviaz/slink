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
    private _policy: CookiePolicy<K>,
  ) {}

  public set(key: K, value: unknown): void {
    const name = this._policy.name(key);
    const cookieValue = this._policy.encode(value);

    if (this._cookieManager.get(name) === cookieValue) {
      return;
    }

    this._cookieManager.setCookie(name, cookieValue, this._policy.options);
  }

  public clear(): void {
    for (const key of this._policy.keys) {
      this._cookieManager.deleteCookie(this._policy.name(key));
    }
  }
}
