import { env } from '$env/dynamic/private';
import { fail, redirect } from '@sveltejs/kit';

import { ApiClient } from '@slink/api/Client';
import { HttpException } from '@slink/api/Exceptions';

import { formData } from '@slink/utils/form/formData';

import type { Action, Actions, PageServerLoad } from './$types';

export const load: PageServerLoad = async ({ request, parent, locals }) => {
  await parent();

  if (locals.user) {
    redirect(302, '/profile');
  }

  if (locals.globalSettings?.user?.allowRegistration === false) {
    redirect(302, '/profile/login');
  }

  const { cookieManager, ...serializableLocals } = locals;
  return serializableLocals;
};

const defaultAction: Action = async ({ fetch, request, cookies, locals }) => {
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

    locals.cookieManager.setCookie(cookies, 'createdUserId', id);

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
