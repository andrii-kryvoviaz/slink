import { env } from '$env/dynamic/private';

import { ApiClient } from '@slink/api/Client';
import { HttpException } from '@slink/api/Exceptions';
import { formData } from '@slink/utils/form/formData';
import { fail, redirect } from '@sveltejs/kit';

import type { Action, Actions, PageServerLoad } from './$types';

export const load: PageServerLoad = async ({ request, parent, locals }) => {
  await parent();

  if (locals.user) {
    redirect(302, '/profile');
  }

  if (locals.globalSettings?.user?.allowRegistration === false) {
    redirect(302, '/profile/login');
  }

  return locals;
};

const defaultAction: Action = async ({ fetch, request, cookies }) => {
  const { username, email, password, confirm } = await formData(request);

  let redirectUrl: string | null = '/profile/login';

  try {
    const response = await ApiClient.use(fetch).auth.signup({
      username,
      email,
      password,
      confirm,
    });

    const { id, headers } = response;

    cookies.set('createdUserId', id, {
      sameSite: 'strict',
      path: '/',
    });

    redirectUrl = headers.get('location');
  } catch (e) {
    if (e instanceof HttpException) {
      return fail(422, {
        username,
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
    redirect(302, redirectUrl);
  }
};

export const actions: Actions = {
  default: defaultAction,
} satisfies Actions;
