import { graceful } from '@slink/utils/async/graceful';
import { urlParamUtils } from '@slink/utils/url';

import type { PageServerLoad } from './$types';

export const load: PageServerLoad = async ({ parent, locals, url }) => {
  const { user } = await parent();

  if (!user) {
    return locals.gateway.redirect();
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
