import { graceful } from '@slink/utils/async/graceful';
import { resolveSearchFilter } from '@slink/utils/url';

import type { PageServerLoad } from './$types';

export const load: PageServerLoad = async ({ parent, locals, url }) => {
  await parent();

  const hasAny = await graceful(
    () =>
      locals.api.image.existsPublicImages(
        resolveSearchFilter(url.searchParams),
      ),
    true,
  );

  return {
    user: locals.user,
    globalSettings: locals.globalSettings,
    hasAny,
  };
};
