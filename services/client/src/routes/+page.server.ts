import { redirect } from '@sveltejs/kit';

import { LandingPage } from '@slink/lib/enum/LandingPage';

import type { PageServerLoad } from './$types';

export const load: PageServerLoad = async ({ locals }) => {
  let landingPage = `/${LandingPage.Explore}`;

  if (locals.userPreferences?.['navigation.landingPage']) {
    landingPage = `/${locals.userPreferences['navigation.landingPage']}`;
  }

  redirect(302, landingPage);
};
