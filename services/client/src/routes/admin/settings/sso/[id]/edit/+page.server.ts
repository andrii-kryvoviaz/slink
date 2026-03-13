import { error } from '@sveltejs/kit';

import type { PageServerLoad } from './$types';

export const load: PageServerLoad = async ({ parent, locals, params }) => {
  await parent();

  const providers = await locals.api.oauth.list();
  const provider = providers.find((p) => p.id === params.id);

  if (!provider) {
    error(404, 'Provider not found');
  }

  return { provider };
};
