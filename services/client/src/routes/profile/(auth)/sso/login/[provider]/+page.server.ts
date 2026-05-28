import { redirect } from '@sveltejs/kit';

import { SsoError } from '@slink/lib/auth/sso';

import type { PageServerLoad } from './$types';

export const load: PageServerLoad = async (event) => {
  const { params, locals } = event;

  try {
    const { authorizationUrl } = await locals.api.sso.authorize({
      provider: params.provider,
    });
    redirect(302, authorizationUrl);
  } catch (e) {
    SsoError.handle(event, e);
  }
};
