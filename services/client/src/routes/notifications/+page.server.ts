import { redirect } from '@sveltejs/kit';

import { graceful } from '@slink/utils/async/graceful';

import type { PageServerLoad } from './$types';

export const load: PageServerLoad = async ({ parent, locals }) => {
  const { user } = await parent();

  if (!user) {
    redirect(302, '/profile/login');
  }

  const hasAny = await graceful(() => locals.api.notification.exists(), true);

  return {
    user,
    hasAny,
  };
};
