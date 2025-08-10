import { fail, redirect } from '@sveltejs/kit';

import { HttpException } from '@slink/api/Exceptions';

import { Auth } from '@slink/lib/auth/Auth';

import { formData } from '@slink/utils/form/formData';

import type { Actions, PageServerLoad } from './$types';

export const load: PageServerLoad = async ({ parent, locals }) => {
  await parent();

  if (locals.user) {
    redirect(302, '/profile');
  }

  const { cookieManager, ...serializableLocals } = locals;
  return serializableLocals;
};

export const actions: Actions = {
  default: async ({ request, cookies, fetch, locals }) => {
    const { username, password } = await formData(request);

    try {
      await Auth.login(
        { username, password },
        { cookies, cookieManager: locals.cookieManager, fetch },
      );
    } catch (e) {
      if (e instanceof HttpException) {
        return fail(422, {
          username,
          errors: e.errors,
        });
      }

      return fail(500, {
        errors: { message: 'Something went wrong. Please try again later.' },
      });
    }

    redirect(302, '/');
  },
} satisfies Actions;
