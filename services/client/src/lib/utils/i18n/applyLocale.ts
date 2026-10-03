import { loadLocale } from 'wuchale/load-utils';

import { refreshAll } from '$app/navigation';

import type { Locale } from '@slink/lib/settings/Settings.enums';
import type { UserSettings } from '@slink/lib/settings/UserSettings.svelte';
import { runtimeTranslator } from '@slink/lib/utils/i18n';

export async function applyLocale(locale: Locale, settings: UserSettings) {
  await loadLocale(locale);
  runtimeTranslator.locale = locale;
  settings.locale.current = locale;
  await refreshAll();
}
