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

  const allowOnlyPublicImages =
    globalSettings?.image?.allowOnlyPublicImages ?? false;

  const preferences = locals.userPreferences ?? {
    'license.default': null,
    'navigation.landingPage': null,
    'image.defaultVisibility': null,
  };

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
    allowOnlyPublicImages,
  };
};

export const actions: Actions = {
  updatePreferences: async ({ request, locals }) => {
    const {
      defaultLicense,
      syncLicenseToImages,
      defaultLandingPage,
      defaultVisibility,
    } = await formData(request);

    try {
      await locals.api.user.updatePreferences({
        defaultLicense: defaultLicense || null,
        syncLicenseToImages: syncLicenseToImages === 'true',
        defaultLandingPage: defaultLandingPage || null,
        defaultVisibility: defaultVisibility || null,
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
