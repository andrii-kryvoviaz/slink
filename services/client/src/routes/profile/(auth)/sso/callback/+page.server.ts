import { isRedirect, redirect } from '@sveltejs/kit';

import { Auth } from '@slink/lib/auth/Auth';
import { extractShortErrorMessage } from '@slink/lib/utils/error/extractErrorMessage';

import type { PageServerLoad } from './$types';

export const load: PageServerLoad = async ({ url, locals, cookies, fetch }) => {
  const code = url.searchParams.get('code');
  const state = url.searchParams.get('state');
  const error = url.searchParams.get('error');
  const errorDescription = url.searchParams.get('error_description');

  if (error) {
    const message = errorDescription || error;
    locals.flash.error(message);
    redirect(302, '/profile/login');
  }

  if (!code || !state) {
    locals.flash.error('Missing authorization parameters');
    redirect(302, '/profile/login');
  }

  try {
    const response = await locals.api.sso.token({
      code,
      state,
    });

    if ('approval_required' in response) {
      locals.cookieManager.setCookie(cookies, 'createdUserId', response.userId);
      redirect(302, '/profile/awaiting-approval');
    }

    const { access_token, refresh_token } = response;

    await Auth.loginWithTokens(
      { accessToken: access_token, refreshToken: refresh_token },
      { cookies, cookieManager: locals.cookieManager, fetch },
    );
  } catch (e) {
    if (isRedirect(e)) throw e;
    const err =
      e instanceof Error ? e : new Error('An unexpected error occurred');
    locals.flash.error(extractShortErrorMessage(err));
    redirect(302, '/profile/login');
  }

  redirect(302, '/');
};
