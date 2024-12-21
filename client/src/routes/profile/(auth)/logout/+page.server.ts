import type { Actions, PageServerLoad } from './$types';
import { redirect } from '@sveltejs/kit';

import { Auth } from '@slink/lib/auth/Auth';

export const load: PageServerLoad = async ({}) => {};

export const actions: Actions = {
  default: async ({ cookies, locals }) => {
    locals.user = null;
    Auth.logout(cookies);

    throw redirect(302, '/profile/login');
  },
};
