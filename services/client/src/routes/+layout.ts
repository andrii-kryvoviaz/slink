import { Application } from '#lib/application/index.js';
import { browser } from '$app/env';

import { runtimeTranslator } from '@slink/lib/utils/i18n/RuntimeTranslator.svelte';
import { initLocale } from '@slink/lib/utils/i18n/initLocale';

import '@slink/utils/string/stringExtensions';

import type { LayoutLoad } from './$types';

export const load: LayoutLoad = async ({ fetch, data }) => {
  await Application.initialize(fetch, data.gatewayUrl);

  const locale = data.settings.locale.current;
  runtimeTranslator.locale = locale;

  if (browser) {
    await initLocale(locale);
  }

  return data;
};
