import { ApiClient } from '@slink/api/Client';

import '@slink/utils/string/stringExtensions';

import type { LayoutLoad } from './$types';

export const load: LayoutLoad = async ({ params, fetch, url }) => {
  // Set API fetch function to the one provided by SvelteKit
  // In order to avoid race condition, the child page load function should await for the parent
  // e.g. await parent();
  ApiClient.use(fetch);
};
