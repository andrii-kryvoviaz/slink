import { isRedirect, redirect } from '@sveltejs/kit';

import { extractShortErrorMessage } from '@slink/lib/utils/error/extractErrorMessage';

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
    const err =
      e instanceof Error ? e : new Error('An unexpected error occurred');
    locals.flash.error(extractShortErrorMessage(err));
    redirect(302, '/profile/login');
  }
};
