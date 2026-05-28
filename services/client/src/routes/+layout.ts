import { browser } from '$app/environment';
import { Application } from '$lib/application';

import { runtimeTranslator } from '@slink/lib/utils/i18n/RuntimeTranslator.svelte';
import { initLocale } from '@slink/lib/utils/i18n/initLocale';

import '@slink/utils/string/stringExtensions';

import type { LayoutLoad } from './$types';

export const load: LayoutLoad = async ({ fetch, data }) => {
  await Application.initialize(fetch);

  const locale = data.locale ?? 'en';
  runtimeTranslator.locale = locale;

  if (browser) {
    await initLocale(locale);
  }

  return data;
};
