import { Auth } from '@slink/lib/auth/Auth';
import { redirect } from '@sveltejs/kit';

import type { Actions, PageServerLoad } from './$types';

export const load: PageServerLoad = async ({}) => {};

export const actions: Actions = {
  default: async ({ cookies }) => {
    Auth.logout(cookies);
  },
};
