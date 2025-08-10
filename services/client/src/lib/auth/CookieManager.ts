import type { Cookies } from '@sveltejs/kit';

type CookieOptions = {
  secure?: boolean;
  sameSite?: 'strict' | 'lax' | 'none';
  path?: string;
  httpOnly?: boolean;
  maxAge?: number;
};

export class CookieManager {
  constructor(private requireSsl: boolean) {}

  public setCookie(
    cookies: Cookies,
    name: string,
    value: string,
    options: CookieOptions = {},
  ): void {
    const cookieOptions: Parameters<Cookies['set']>[2] = {
      sameSite: options.sameSite ?? 'strict',
      path: options.path ?? '/',
      secure: this.requireSsl ? true : options.secure,
      httpOnly: options.httpOnly,
      maxAge: options.maxAge,
    };

    cookies.set(name, value, cookieOptions);
  }

  public deleteCookie(
    cookies: Cookies,
    name: string,
    options: Pick<CookieOptions, 'path'> = {},
  ): void {
    const cookieOptions: Parameters<Cookies['delete']>[1] = {
      sameSite: 'strict',
      path: options.path ?? '/',
      secure: this.requireSsl,
    };

    cookies.delete(name, cookieOptions);
  }
}
