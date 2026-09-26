import { redirect } from '@sveltejs/kit';

import type { PageServerLoad } from './$types';

export const load: PageServerLoad = async ({ cookies, locals }) => {
  if (locals.user) {
    redirect(302, '/profile');
  }

  const userId = cookies.get('createdUserId');

  if (!userId) {
    return locals.gateway.redirect();
  }

  const response = await locals.api.user.checkStatus(userId);
  const { status } = response;

  return {
    userId,
    status,
  };
};
