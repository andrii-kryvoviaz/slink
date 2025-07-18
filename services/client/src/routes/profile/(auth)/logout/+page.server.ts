import { redirect } from '@sveltejs/kit';

import { Auth } from '@slink/lib/auth/Auth';

import type { Actions, PageServerLoad } from './$types';

export const load: PageServerLoad = async ({}) => {};

export const actions: Actions = {
  default: async ({ cookies, fetch, locals }) => {
    locals.user = null;
    Auth.arguments(fetch).logout(cookies);

    redirect(302, '/profile/login');
  },
};
