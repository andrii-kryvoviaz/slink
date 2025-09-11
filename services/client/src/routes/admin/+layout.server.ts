import { redirect } from '@sveltejs/kit';

import type { LayoutServerLoad } from './$types';

export const load: LayoutServerLoad = async ({ locals }) => {
  const { settings, user } = locals;

  if (!user?.roles.includes('ROLE_ADMIN')) {
    redirect(302, '/profile/login');
  }

  return {
    settings,
    user,
  };
};
