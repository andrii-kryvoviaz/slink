import { env } from '$env/dynamic/private';
import { fail, redirect } from '@sveltejs/kit';

import { ApiClient } from '@slink/api/Client';
import { HttpException } from '@slink/api/Exceptions';

import { formData } from '@slink/utils/form/formData';

import type { Actions } from '../../../.svelte-kit/types/src/routes/profile/(auth)/login/$types';
import type { PageServerLoad } from './$types';

export const load: PageServerLoad = async ({ locals }) => {
  const { user, settings } = locals;

  if (!user) {
    throw redirect(302, '/profile/login');
  }

  return {
    user,
    settings,
  };
};

export const actions: Actions = {
  changePassword: async ({ request, cookies, fetch }) => {
    const { old_password, password, confirm } = await formData(request);

    try {
      await ApiClient.user.changePassword({
        old_password,
        password,
        confirm,
      });
    } catch (e) {
      if (e instanceof HttpException) {
        return fail(422, {
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

    return {
      success: true,
    };
  },
} satisfies Actions;
