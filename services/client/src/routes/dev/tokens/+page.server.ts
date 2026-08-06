import { error } from '@sveltejs/kit';

import { dev } from '$app/environment';

import { isAdmin } from '@slink/lib/auth/utils';

import type { PageServerLoad } from './$types';

export const load: PageServerLoad = ({ locals }) => {
  if (!dev && !isAdmin(locals.user)) {
    error(404);
  }

  return {};
};
