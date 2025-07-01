import { redirect } from '@sveltejs/kit';

import { ApiClient } from '@slink/api/Client';

import type { PageServerLoad } from './$types';

export const load: PageServerLoad = async ({ cookies, locals }) => {
  const userId = cookies.get('createdUserId');

  if (!userId) {
    throw redirect(302, '/profile/login');
  }

  if (locals.user) {
    throw redirect(302, '/profile');
  }

  const response = await ApiClient.user.checkStatus(userId);
  const { status } = response;

  return {
    userId,
    status,
  };
};
