import { fail, redirect } from '@sveltejs/kit';

import { ApiClient } from '@slink/api/Client';
import { HttpException } from '@slink/api/Exceptions';

import { formData } from '@slink/utils/form/formData';

import type { Actions, PageServerLoad } from './$types';

export const load: PageServerLoad = async ({ locals, parent, fetch }) => {
  await parent();

  const { user, globalSettings } = locals;

  if (!user) {
    redirect(302, '/profile/login');
  }

  const licensingEnabled = globalSettings?.image?.enableLicensing ?? false;

  if (!licensingEnabled) {
    redirect(302, '/profile');
  }

  let preferences = { defaultLicense: null as string | null };
  let licenses: {
    id: string;
    title: string;
    name: string;
    description: string;
    url: string | null;
  }[] = [];

  try {
    const [prefsResponse, licensesResponse] = await Promise.all([
      ApiClient.use(fetch).user.getPreferences(),
      ApiClient.use(fetch).image.getLicenses(),
    ]);
    preferences = prefsResponse;
    licenses = licensesResponse.licenses ?? [];
  } catch (e) {
    console.error('Failed to load preferences data:', e);
  }

  return {
    user,
    preferences,
    licenses,
  };
};

export const actions: Actions = {
  updatePreferences: async ({ request, fetch }) => {
    const { defaultLicense, syncLicenseToImages } = await formData(request);

    try {
      await ApiClient.use(fetch).user.updatePreferences({
        defaultLicense: defaultLicense || null,
        syncLicenseToImages: syncLicenseToImages === 'true',
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
