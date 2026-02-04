import { Application } from '$lib/application';

import '@slink/utils/string/stringExtensions';

import type { LayoutLoad } from './$types';

export const load: LayoutLoad = async ({ fetch, data }) => {
  const app = new Application();

  await app.initialize();
  app.setupApiClient(fetch);

  return data;
};
