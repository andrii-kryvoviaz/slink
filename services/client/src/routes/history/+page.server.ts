import { graceful } from '@slink/utils/async/graceful';
import { urlParamUtils } from '@slink/utils/url';

import type { PageServerLoad } from './$types';

export const load: PageServerLoad = async ({ parent, locals, url }) => {
  const { user } = await parent();

  if (!user) {
    return locals.gateway.redirect();
  }

  const { searchTerm, searchBy } = urlParamUtils.fromPage(url).searchFilter();
  const tagIds = url.searchParams.getAll('tagIds');
  const requireAllTags =
    url.searchParams.get('requireAllTags') === 'true' || undefined;

  const hasAny = await graceful(
    () =>
      locals.api.image.existsHistory({
        searchTerm,
        searchBy,
        tagIds: tagIds.length > 0 ? tagIds : undefined,
        requireAllTags,
      }),
    true,
  );

  return {
    user,
    hasAny,
  };
};
