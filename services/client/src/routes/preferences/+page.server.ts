import { fail } from '@sveltejs/kit';

import { HttpException } from '@slink/api/Exceptions';

import type { Actions, PageServerLoad } from './$types';

export const load: PageServerLoad = async ({ locals, parent }) => {
  await parent();

  const { user, globalSettings } = locals;

  if (!user) {
    return locals.gateway.redirect();
  }

  const licensingEnabled = globalSettings?.image?.enableLicensing ?? false;

  const preferences = locals.userPreferences ?? null;

  let licenses: {
    id: string;
    title: string;
    name: string;
    description: string;
    url: string | null;
  }[] = [];

  if (licensingEnabled) {
    try {
      const licensesResponse = await locals.api.image.getLicenses();
      licenses = licensesResponse.licenses ?? [];
    } catch (e) {
      console.error('Failed to load licenses:', e);
    }
  }

  return {
    user,
    preferences,
    licenses,
    licensingEnabled,
    uploadPolicy: locals.uploadPolicy,
  };
};

export const actions: Actions = {
  updatePreferences: async ({ request, locals }) => {
    const form = await request.formData();

    try {
      await locals.api.user.updatePreferences(Object.fromEntries([...form]));
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
      preferencesWasUpdated: true,
    };
  },
} satisfies Actions;
