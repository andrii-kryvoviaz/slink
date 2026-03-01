import { fail, redirect } from '@sveltejs/kit';

import { HttpException } from '@slink/api/Exceptions';
import type { SsoProvider } from '@slink/api/Resources/SsoResource';

import { Auth } from '@slink/lib/auth/Auth';

import { formData } from '@slink/utils/form/formData';

import type { Actions, PageServerLoad } from './$types';

export const load: PageServerLoad = async ({ parent, locals, url }) => {
  await parent();

  if (locals.user) {
    redirect(302, '/profile');
  }

  const ssoProviders: SsoProvider[] = await locals.api.sso.getProviders();
  const sso = url.searchParams.get('sso_error');

  if (sso) {
    console.error(`[SSO] Login error: ${sso}`);
  }

  const { cookieManager, ...serializableLocals } = locals;
  return { ...serializableLocals, ssoProviders, error: { sso } };
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
