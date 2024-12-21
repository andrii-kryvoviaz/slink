import type { LayoutLoad } from './$types';
import '@slink/utils/string/stringExtensions';

import { browser } from '$app/environment';

import { ApiClient } from '@slink/api/Client';
import { themeIcons } from '@slink/theme.icons';
import { preloadIconSet } from '@slink/utils/ui/preloadIconSet';
import { redirect } from '@sveltejs/kit';

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

  // Preload the theme icons to avoid flickering
  preloadIconSet(themeIcons);

  // pass any data from ssr to the client
  return data;
};
