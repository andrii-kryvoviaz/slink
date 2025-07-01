import { error } from '@sveltejs/kit';

import type { LayoutServerLoad } from './$types';

export const load: LayoutServerLoad = async ({ locals }) => {
  const { settings, user } = locals;

  if (!user?.roles.includes('ROLE_ADMIN')) {
    error(403, {
      message: 'You do not have permission to access this page.',
    });
  }

  return {
    settings,
    user,
  };
};
