import { graceful } from '@slink/utils/async/graceful';
import { urlParamUtils } from '@slink/utils/url';

import type { PageServerLoad } from './$types';

export const load: PageServerLoad = async ({ parent, locals, url }) => {
  const { user } = await parent();

  if (!user) {
    return locals.gateway.redirect();
  }

  const { searchTerm } = urlParamUtils.fromPage(url).searchFilter();
  const parentId = url.searchParams.get('parentId') ?? undefined;
  const rootOnly = url.searchParams.get('rootOnly') === 'true' || undefined;
  const ids = url.searchParams.getAll('ids[]');

  const hasAny = await graceful(
    () =>
      locals.api.tag.exists({
        parentId,
        searchTerm,
        rootOnly,
        ids: ids.length > 0 ? ids : undefined,
      }),
    true,
  );

  return {
    user,
    hasAny,
  };
};
