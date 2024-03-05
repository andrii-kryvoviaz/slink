import { redirect } from '@sveltejs/kit';

import type { PageServerLoad } from './$types';

export const load: PageServerLoad = async ({ parent, locals }) => {
  await parent();

  if (!locals.user) {
    throw redirect(302, '/profile/login');
  }
};
