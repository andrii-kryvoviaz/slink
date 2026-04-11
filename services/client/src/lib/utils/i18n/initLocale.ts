import { locales } from '@slink/locales/data.js';
import '@slink/locales/js.loader.js';
import '@slink/locales/main.loader.svelte.js';
import { loadLocale } from 'wuchale/load-utils';

import { runtimeTranslator } from '@slink/lib/utils/i18n/RuntimeTranslator.svelte';

export async function initLocale(locale: string): Promise<void> {
  const resolved = locale as (typeof locales)[number];

  if (locales.includes(resolved)) {
    await loadLocale(resolved);
  }

  runtimeTranslator.locale = resolved;
}
