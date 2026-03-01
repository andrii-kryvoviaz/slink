import { isRedirect, redirect } from '@sveltejs/kit';

import { Auth } from '@slink/lib/auth/Auth';

import type { PageServerLoad } from './$types';

export const load: PageServerLoad = async ({ url, locals, cookies, fetch }) => {
  const code = url.searchParams.get('code');
  const state = url.searchParams.get('state');
  const error = url.searchParams.get('error');
  const errorDescription = url.searchParams.get('error_description');

  if (error) {
    const message = errorDescription || error;
    redirect(302, `/profile/login?sso_error=${encodeURIComponent(message)}`);
  }

  if (!code || !state) {
    redirect(302, '/profile/login?sso_error=Missing+authorization+parameters');
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
    redirect(
      302,
      '/profile/login?sso_error=Authentication+failed.+Please+try+again.',
    );
  }

  redirect(302, '/');
};
