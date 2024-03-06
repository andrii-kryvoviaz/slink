import { env } from '$env/dynamic/private';
import { fail, redirect } from '@sveltejs/kit';

import { ApiClient } from '@slink/api/Client';
import { HttpException } from '@slink/api/Exceptions';

import { formData } from '@slink/utils/form/formData';

import type { Action, Actions, PageServerLoad } from './$types';

export const load: PageServerLoad = async ({ request, parent, locals }) => {
  await parent();

  if (locals.user) {
    throw redirect(302, '/profile');
  }

  return locals;
};

const defaultAction: Action = async ({ locals, request, cookies }) => {
  const { display_name, email, password, confirm } = await formData(request);

  let redirectUrl: string | null = '/profile/login';

  try {
    const response = await ApiClient.auth.signup({
      display_name,
      email,
      password,
      confirm,
    });

    const { id, headers } = response;

    cookies.set('createdUserId', id, {
      sameSite: 'strict',
      path: '/',
      httpOnly: true,
      secure: true,
    });

    redirectUrl = headers.get('location');
  } catch (e) {
    if (e instanceof HttpException) {
      return fail(422, {
        display_name,
        email,
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

  if (redirectUrl) {
    throw redirect(302, redirectUrl);
  }
};

export const actions: Actions = {
  default: defaultAction,
} satisfies Actions;
