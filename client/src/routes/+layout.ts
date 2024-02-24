import { themeIcons } from '@slink/theme.icons';

import { ApiClient } from '@slink/api/Client';

import '@slink/utils/string/stringExtensions';
import { preloadIconSet } from '@slink/utils/ui/preloadIconSet';

import type { LayoutLoad } from './$types';

export const load: LayoutLoad = async ({ fetch, data }) => {
  // Set API fetch function to the one provided by SvelteKit
  // In order to avoid race condition, the child page load function should await for the parent
  // e.g. await parent();
  ApiClient.use(fetch);

  // Preload the theme icons to avoid flickering
  preloadIconSet(themeIcons);

  // pass any data from ssr to the client
  return data;
};
