import { isRedirect, redirect } from '@sveltejs/kit';

import type { PageServerLoad } from './$types';

export const load: PageServerLoad = async ({ params, url, locals }) => {
  const { provider } = params;
  const redirectUri = `${url.origin}/profile/sso/callback`;

  try {
    const { authorizationUrl } = await locals.api.sso.authorize({
      provider,
      redirectUri,
    });

    redirect(302, authorizationUrl);
  } catch (e) {
    if (isRedirect(e)) throw e;
    redirect(
      302,
      '/profile/login?sso_error=SSO+provider+is+currently+unavailable.+Please+try+again+later.',
    );
  }
};
