import '@slink/utils/string/stringExtensions';

import { browser } from '$app/environment';

import { ApiClient } from '@slink/api/Client';
import { themeIcons } from '@slink/theme.icons';
import { preloadIconSet } from '@slink/utils/ui/preloadIconSet';
import { error, redirect } from '@sveltejs/kit';

import type { LayoutLoad } from './$types';

export const load: LayoutLoad = async ({ fetch, data }) => {
  ApiClient.use(fetch);

  ApiClient.on('unauthorized', () => {
    if (!browser) {
      redirect(302, '/profile/login');
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
