import { redirect } from '@sveltejs/kit';

import { graceful } from '@slink/utils/async/graceful';

import type { PageServerLoad } from './$types';

export const load: PageServerLoad = async ({ parent, locals, url }) => {
  const { user } = await parent();

  if (!user) {
    redirect(302, '/profile/login');
  }

  const searchTerm = url.searchParams.get('search') ?? undefined;
  const searchBy = url.searchParams.get('searchBy') ?? undefined;
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
