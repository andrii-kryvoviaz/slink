import type { Cookies } from '@sveltejs/kit';

import { type CookiePolicy, ScopedCookies } from '@slink/lib/auth/CookiePolicy';

export type CookieOptions = {
  secure?: boolean;
  sameSite?: 'strict' | 'lax' | 'none';
  path?: string;
  httpOnly?: boolean;
  maxAge?: number;
};

export class CookieManager {
  constructor(
    private requireSsl: boolean,
    private _cookies: Cookies,
  ) {}

  public get(name: string): string | undefined {
    return this._cookies.get(name);
  }

  public setCookie(
    name: string,
    value: string,
    options: CookieOptions = {},
  ): void {
    const cookieOptions: Parameters<Cookies['set']>[2] = {
      sameSite: options.sameSite ?? 'lax',
      path: options.path ?? '/',
      secure: this.requireSsl ? true : options.secure,
      httpOnly: options.httpOnly,
      maxAge: options.maxAge,
    };

    this._cookies.set(name, value, cookieOptions);
  }

  public deleteCookie(
    name: string,
    options: Pick<CookieOptions, 'path' | 'sameSite'> = {},
  ): void {
    const cookieOptions: Parameters<Cookies['delete']>[1] = {
      sameSite: options.sameSite ?? 'lax',
      path: options.path ?? '/',
      secure: this.requireSsl,
    };

    this._cookies.delete(name, cookieOptions);
  }

  public use<K extends string>(policy: CookiePolicy<K>): ScopedCookies<K> {
    return new ScopedCookies(this, policy);
  }
}
