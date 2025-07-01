import { themeIcons } from '@slink/theme.icons';
import { error, redirect } from '@sveltejs/kit';

import { browser } from '$app/environment';

import { ApiClient } from '@slink/api/Client';

import '@slink/utils/string/stringExtensions';
import { preloadIconSet } from '@slink/utils/ui/preloadIconSet';

import type { LayoutLoad } from './$types';

export const load: LayoutLoad = async ({ fetch, data }) => {
  ApiClient.use(fetch);

  ApiClient.on('unauthorized', () => {
    if (!browser) {
      throw redirect(302, '/profile/login');
    }
  });

  ApiClient.on('forbidden', () => {
    error(403, {
      message: 'You do not have permission to access this page.',
    });
  });

  preloadIconSet(themeIcons);

  return data;
};
