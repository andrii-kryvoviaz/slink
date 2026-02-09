import { redirect } from '@sveltejs/kit';

import { LandingPage } from '@slink/lib/enum/LandingPage';

import type { PageServerLoad } from './$types';

export const load: PageServerLoad = async ({ locals }) => {
  let landingPage = `/${LandingPage.Explore}`;

  if (locals.user) {
    try {
      const preferences = await locals.api.user.getPreferences();
      if (preferences['navigation.landingPage']) {
        landingPage = `/${preferences['navigation.landingPage']}`;
      }
    } catch (e) {
      console.warn('Failed to load user preferences for redirect:', e);
    }
  }

  redirect(302, landingPage);
};
