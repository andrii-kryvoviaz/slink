import type { PageServerLoad } from './$types';

export const load: PageServerLoad = async ({
  parent,
  locals,
  depends,
  untrack,
}) => {
  depends('app:sso-providers');
  await untrack(() => parent());

  return { providers: locals.api.oauth.list() };
};
