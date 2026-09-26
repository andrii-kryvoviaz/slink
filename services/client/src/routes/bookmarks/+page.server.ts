import { graceful } from '@slink/utils/async/graceful';

import type { PageServerLoad } from './$types';

export const load: PageServerLoad = async ({ parent, locals }) => {
  const { user } = await parent();

  if (!user) {
    return locals.gateway.redirect();
  }

  const hasAny = await graceful(() => locals.api.bookmark.exists(), true);

  return {
    user,
    hasAny,
  };
};
