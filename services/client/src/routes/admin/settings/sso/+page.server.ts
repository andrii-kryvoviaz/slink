import type { PageServerLoad } from './$types';

export const load: PageServerLoad = async ({
  parent,
  locals,
  url,
  depends,
}) => {
  depends('app:sso-providers');
  await parent();

  const callbackUrl = `${url.origin}/profile/sso/callback`;

  return { providers: locals.api.oauth.list(), callbackUrl };
};
