import { redirect } from '@sveltejs/kit';

import { ApiClient } from '@slink/api/Client';

import type { PageServerLoad } from './$types';

export const load: PageServerLoad = async ({ parent, locals }) => {
  await parent();

  if (!locals.user) {
    throw redirect(302, '/profile/login');
  }

  const page = 1;
  const limit = 10;

  const { data: images, meta } = await ApiClient.image.getHistory(page, limit);

  return {
    images,
    meta,
    page,
    limit,
  };
};
