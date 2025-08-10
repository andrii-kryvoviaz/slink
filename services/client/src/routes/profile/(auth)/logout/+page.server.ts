import { redirect } from '@sveltejs/kit';

import { Auth } from '@slink/lib/auth/Auth';

import type { Actions, PageServerLoad } from './$types';

export const load: PageServerLoad = async ({}) => {};

export const actions: Actions = {
  default: async ({ cookies, fetch, locals }) => {
    locals.user = null;
    Auth.logout({ cookies, cookieManager: locals.cookieManager, fetch });

    redirect(302, '/profile/login');
  },
};
