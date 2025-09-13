import { redirect } from '@sveltejs/kit';

import { isAdmin } from '@slink/lib/auth/utils';

import type { LayoutServerLoad } from './$types';

export const load: LayoutServerLoad = async ({ locals }) => {
  const { settings, user } = locals;

  if (!isAdmin(user)) {
    redirect(302, '/profile/login');
  }

  return {
    settings,
    user,
  };
};
