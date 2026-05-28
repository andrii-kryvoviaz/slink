import { type RequestEvent, isRedirect, redirect } from '@sveltejs/kit';

import { extractShortErrorMessage } from '@slink/lib/utils/error/extractErrorMessage';
import { t } from '@slink/lib/utils/i18n';

export class SsoError {
  private static readonly COOKIE_NAME = 'sso_error';
  private static readonly LOGIN_PATH = '/profile/login';

  private constructor() {}

  public static redirect(
    { cookies, locals }: RequestEvent,
    message: string,
  ): never {
    locals.cookieManager.setCookie(cookies, SsoError.COOKIE_NAME, message, {
      httpOnly: true,
    });

    redirect(302, SsoError.LOGIN_PATH);
  }

  public static consume({ cookies, locals }: RequestEvent): string | null {
    const value = cookies.get(SsoError.COOKIE_NAME);
    if (!value) {
      return null;
    }

    locals.cookieManager.deleteCookie(cookies, SsoError.COOKIE_NAME);
    return value;
  }

  public static handle(event: RequestEvent, e: unknown): never {
    if (isRedirect(e)) {
      throw e;
    }

    let err: Error;
    if (e instanceof Error) {
      err = e;
    } else {
      err = new Error('An unexpected error occurred');
    }

    SsoError.redirect(event, t(extractShortErrorMessage(err)));
  }
}
