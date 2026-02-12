import { Application } from '$lib/application';

import '@slink/utils/string/stringExtensions';

import type { LayoutLoad } from './$types';

export const load: LayoutLoad = async ({ fetch, data }) => {
  await Application.initialize(fetch);
  return data;
};
