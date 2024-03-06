import { env } from '$env/dynamic/private';
import { Auth } from '@slink/lib/auth/Auth';
import { fail, redirect } from '@sveltejs/kit';

import { HttpException } from '@slink/api/Exceptions';

import { formData } from '@slink/utils/form/formData';

import type { Actions, PageServerLoad } from './$types';

export const load: PageServerLoad = async ({ request, parent, locals }) => {
  await parent();

  if (locals.user) {
    throw redirect(302, '/profile');
  }

  return locals;
};

export const actions: Actions = {
  default: async ({ request, cookies, fetch }) => {
    const { username, password } = await formData(request);

    try {
      await Auth.login({ username, password }, { cookies, fetch });
    } catch (e) {
      if (e instanceof HttpException) {
        return fail(422, {
          username,
          errors: e.errors,
        });
      }

      if (env.NODE_ENV === 'development') {
        console.error(e);
      }

      return fail(500, {
        errors: { message: 'Something went wrong. Please try again later.' },
      });
    }

    throw redirect(302, '/');
  },
} satisfies Actions;
