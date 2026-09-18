import { redirect } from '@sveltejs/kit';

import { graceful } from '@slink/utils/async/graceful';
import { urlParamUtils } from '@slink/utils/url';

import type { PageServerLoad } from './$types';

export const load: PageServerLoad = async ({ parent, locals, url }) => {
  const { user } = await parent();

  if (!user) {
    redirect(302, '/profile/login');
  }

  const { searchTerm } = urlParamUtils.fromPage(url).searchFilter();

  const hasAny = await graceful(
    () => locals.api.collection.exists({ searchTerm }),
    true,
  );

  return {
    user,
    hasAny,
  };
};
