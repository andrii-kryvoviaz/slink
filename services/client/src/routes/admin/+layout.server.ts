import { redirect } from '@sveltejs/kit';

import { isAdmin } from '@slink/lib/auth/utils';

import { authRoutes } from '@slink/utils/url/routes/auth';

import type { LayoutServerLoad } from './$types';

export const load: LayoutServerLoad = async ({ locals }) => {
  const { settings, user } = locals;

  if (!user) {
    return locals.gateway.redirect();
  }

  if (!isAdmin(user)) {
    redirect(302, authRoutes.login);
  }

  return {
    settings,
    user,
  };
};
