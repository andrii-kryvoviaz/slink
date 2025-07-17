import { Auth } from '@slink/lib/auth/Auth';
import { redirect } from '@sveltejs/kit';

import type { Actions, PageServerLoad } from './$types';

export const load: PageServerLoad = async ({}) => {};

export const actions: Actions = {
  default: async ({ cookies, locals }) => {
    locals.user = null;
    Auth.logout(cookies);

    redirect(302, '/profile/login');
  },
};
