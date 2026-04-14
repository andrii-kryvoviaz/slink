import { browser } from '$app/environment';
import { Application } from '$lib/application';

import { initLocale } from '@slink/lib/utils/i18n/initLocale';

import '@slink/utils/string/stringExtensions';

import type { LayoutLoad } from './$types';

export const load: LayoutLoad = async ({ fetch, data }) => {
  await Application.initialize(fetch);

  if (browser) {
    await initLocale(data.locale ?? 'en');
  }

  return data;
};
