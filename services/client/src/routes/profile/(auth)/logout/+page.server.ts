import { redirect } from '@sveltejs/kit';

import { Auth } from '@slink/lib/auth/Auth';

import { authRoutes } from '@slink/utils/url/routes/auth';

import type { Actions } from './$types';

export const actions: Actions = {
  default: async ({ cookies, fetch, locals }) => {
    locals.user = null;
    await Auth.logout({ cookies, cookieManager: locals.cookies, fetch });

    redirect(302, authRoutes.login);
  },
};
