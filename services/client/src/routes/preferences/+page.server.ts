import { fail, redirect } from '@sveltejs/kit';

import { HttpException } from '@slink/api/Exceptions';

import { formData } from '@slink/utils/form/formData';

import type { Actions, PageServerLoad } from './$types';

export const load: PageServerLoad = async ({ locals, parent }) => {
  await parent();

  const { user, globalSettings } = locals;

  if (!user) {
    redirect(302, '/profile/login');
  }

  const licensingEnabled = globalSettings?.image?.enableLicensing ?? false;

  let preferences = {
    'license.default': null as string | null,
    'navigation.landingPage': null as string | null,
  };
  let licenses: {
    id: string;
    title: string;
    name: string;
    description: string;
    url: string | null;
  }[] = [];

  try {
    const prefsResponse = await locals.api.user.getPreferences();
    preferences = prefsResponse;

    if (licensingEnabled) {
      const licensesResponse = await locals.api.image.getLicenses();
      licenses = licensesResponse.licenses ?? [];
    }
  } catch (e) {
    console.error('Failed to load preferences data:', e);
  }

  return {
    user,
    preferences,
    licenses,
    licensingEnabled,
  };
};

export const actions: Actions = {
  updatePreferences: async ({ request, locals }) => {
    const { defaultLicense, syncLicenseToImages, defaultLandingPage } =
      await formData(request);

    try {
      await locals.api.user.updatePreferences({
        defaultLicense: defaultLicense || null,
        syncLicenseToImages: syncLicenseToImages === 'true',
        defaultLandingPage: defaultLandingPage || null,
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
      preferencesWasUpdated: true,
    };
  },
} satisfies Actions;
