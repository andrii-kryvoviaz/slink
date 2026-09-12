import { fail, redirect } from '@sveltejs/kit';

import { HttpException } from '@slink/api/Exceptions';
import type { UserPreferencesPatch } from '@slink/api/Response';

import { formData } from '@slink/utils/form/formData';

import type { Actions, PageServerLoad } from './$types';

export const load: PageServerLoad = async ({ locals, parent }) => {
  await parent();

  const { user, globalSettings } = locals;

  if (!user) {
    redirect(302, '/profile/login');
  }

  const licensingEnabled = globalSettings?.image?.enableLicensing ?? false;

  const preferences = locals.userPreferences ?? {
    'license.default': null,
    'navigation.landingPage': null,
    'image.defaultVisibility': null,
    'image.stripExifMetadataOverride': null,
    'image.externalUploadAutoPublish': null,
    'display.language': null,
    'display.theme': null,
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
    uploadPolicy: locals.uploadPolicy,
  };
};

export const actions: Actions = {
  updatePreferences: async ({ request, locals }) => {
    const {
      defaultLicense,
      syncLicenseToImages,
      defaultLandingPage,
      defaultVisibility,
      exifMetadataPreference,
      externalUploadAutoPublish,
      displayLanguage,
      displayTheme,
    } = await formData(request);

    try {
      await locals.api.user.updatePreferences({
        'license.default': defaultLicense || null,
        'license.syncToImages': syncLicenseToImages === 'true',
        'navigation.landingPage': defaultLandingPage || null,
        'image.defaultVisibility': defaultVisibility || null,
        'image.stripExifMetadataOverride': exifMetadataPreference || null,
        'image.externalUploadAutoPublish':
          externalUploadAutoPublish === undefined
            ? null
            : externalUploadAutoPublish === 'true',
        'display.language': displayLanguage || null,
        'display.theme': displayTheme || null,
      } as UserPreferencesPatch);
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
