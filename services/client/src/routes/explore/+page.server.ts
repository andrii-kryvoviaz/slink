import { graceful } from '@slink/utils/async/graceful';

import type { PageServerLoad } from './$types';

export const load: PageServerLoad = async ({ parent, locals, url }) => {
  await parent();

  const searchTerm = url.searchParams.get('search') ?? undefined;
  const searchBy = url.searchParams.get('searchBy') ?? undefined;

  const hasAny = await graceful(
    () => locals.api.image.existsPublicImages({ searchTerm, searchBy }),
    true,
  );

  return {
    user: locals.user,
    globalSettings: locals.globalSettings,
    hasAny,
  };
};
