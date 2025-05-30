import type { LayoutLoad } from './$types';
import { themeIcons } from '@slink/theme.icons';
import { error, redirect } from '@sveltejs/kit';

import { browser } from '$app/environment';

import { ApiClient } from '@slink/api/Client';

import '@slink/utils/string/stringExtensions';
import { preloadIconSet } from '@slink/utils/ui/preloadIconSet';

export const load: LayoutLoad = async ({ fetch, data }) => {
  // Set API fetch function to the one provided by SvelteKit
  // In order to avoid race condition, the child page load function should await for the parent
  // e.g. await parent();
  ApiClient.use(fetch);

  // Set Unauthorized handler
  ApiClient.on('unauthorized', () => {
    // Redirect to login page
    if (!browser) {
      throw redirect(302, '/profile/login');
    }
  });

  ApiClient.on('forbidden', () => {
    error(403, {
      message: 'You do not have permission to access this page.',
    });
  });

  // Preload the theme icons to avoid flickering
  preloadIconSet(themeIcons);

  return data;
};
