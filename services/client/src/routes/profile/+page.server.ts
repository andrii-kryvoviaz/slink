import { fail, redirect } from '@sveltejs/kit';

import { ApiClient } from '@slink/api/Client';
import { HttpException } from '@slink/api/Exceptions';

import { Session } from '@slink/lib/auth/Session';
import type { User } from '@slink/lib/auth/Type/User';

import { formData } from '@slink/utils/form/formData';

import type { Actions } from './$types';
import type { PageServerLoad } from './$types';

export const load: PageServerLoad = async ({ locals, parent }) => {
  await parent();

  const { user, settings } = locals;

  if (!user) {
    redirect(302, '/profile/login');
  }

  return {
    user,
    settings,
  };
};

export const actions: Actions = {
  changePassword: async ({ request, fetch }) => {
    const { old_password, password, confirm } = await formData(request);

    try {
      await ApiClient.use(fetch).user.changePassword({
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

      return fail(500, {
        errors: { message: 'Something went wrong. Please try again later.' },
      });
    }

    return {
      passwordWasChanged: true,
    };
  },
  updateProfile: async ({ request, locals, cookies, fetch }) => {
    const { user } = locals;
    const { display_name } = await formData(request);

    if (user?.displayName === display_name) {
      return {
        errors: {
          display_name: 'Display name is the same as the current one.',
        },
        displayName: display_name,
      };
    }

    try {
      await ApiClient.use(fetch).user.updateProfile({
        display_name,
      });
    } catch (e) {
      if (e instanceof HttpException) {
        return fail(422, {
          errors: e.errors,
          displayName: display_name,
        });
      }

      return fail(500, {
        errors: { message: 'Something went wrong. Please try again later.' },
      });
    }

    await Session.set(cookies.get('sessionId'), {
      user: {
        ...(user as User),
        displayName: display_name,
      },
    });

    return {
      profileWasUpdated: true,
      displayName: display_name,
    };
  },
} satisfies Actions;
