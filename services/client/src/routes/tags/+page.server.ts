import { redirect } from '@sveltejs/kit';

import { graceful } from '@slink/utils/async/graceful';

import type { PageServerLoad } from './$types';

export const load: PageServerLoad = async ({ parent, locals, url }) => {
  const { user } = await parent();

  if (!user) {
    redirect(302, '/profile/login');
  }

  const searchTerm = url.searchParams.get('search') ?? undefined;
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
