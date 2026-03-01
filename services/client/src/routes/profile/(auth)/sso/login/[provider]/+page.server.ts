import { redirect } from '@sveltejs/kit';

import type { PageServerLoad } from './$types';

export const load: PageServerLoad = async ({ params, url, locals }) => {
  const { provider } = params;
  const redirectUri = `${url.origin}/profile/sso/callback`;

  const { authorizationUrl } = await locals.api.sso.authorize({
    provider,
    redirectUri,
  });

  redirect(302, authorizationUrl);
};
