import { redirect } from '@sveltejs/kit';

import { Auth } from '@slink/lib/auth/Auth';
import { SsoError } from '@slink/lib/auth/sso';
import { localize } from '@slink/lib/utils/i18n';

import type { PageServerLoad } from './$types';

export const load: PageServerLoad = async (event) => {
  const { url, locals, cookies, fetch } = event;

  const code = url.searchParams.get('code');
  const state = url.searchParams.get('state');
  const error = url.searchParams.get('error');
  const errorDescription = url.searchParams.get('error_description');

  if (error) {
    SsoError.redirect(event, errorDescription || error);
  }

  if (!code || !state) {
    SsoError.redirect(event, localize('Missing authorization parameters'));
  }

  try {
    const response = await locals.api.sso.token({
      code: code as string,
      state: state as string,
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
    SsoError.handle(event, e);
  }

  redirect(302, '/');
};
