import type { Page } from '@playwright/test';

import type { ApiClient } from './api';

const LOCALE_COOKIE = 'settings.locale';

export class Locale {
  constructor(
    private page: Page,
    private api: ApiClient,
  ) {}

  async set(language: string) {
    await this.api.preferences.updatePreferences({
      'display.language': language,
    });
    await this.page.context().addCookies([
      {
        name: LOCALE_COOKIE,
        value: language,
        domain: 'localhost',
        path: '/',
      },
    ]);
  }

  async reset() {
    await this.set('en');
  }

  async read(): Promise<string | null> {
    const cookies = await this.page.context().cookies();
    return cookies.find((c) => c.name === LOCALE_COOKIE)?.value ?? null;
  }
}
