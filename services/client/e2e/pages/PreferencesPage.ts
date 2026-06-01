import { type Page, expect } from '@playwright/test';

import { BasePage } from './BasePage';

const LOCALE_LABELS: Record<string, string> = {
  en: 'English',
  de: 'Deutsch',
  es: 'Español',
  fr: 'Français',
  it: 'Italiano',
  pl: 'Polski',
  uk: 'Українська',
  ja: '日本語',
  zh: '中文',
};

export class PreferencesPage extends BasePage {
  static readonly URL = '/preferences';

  readonly localeTrigger = this.page
    .locator('[data-slot="select-trigger"]')
    .first();
  readonly saveButton = this.page.locator('button[type="submit"]:visible');

  constructor(page: Page) {
    super(page);
  }

  get heading() {
    return this.page.getByRole('heading', { name: 'Preferences' });
  }

  async goto() {
    await this.page.goto(PreferencesPage.URL);
  }

  async selectLocale(value: string) {
    const label = LOCALE_LABELS[value] ?? value;
    const option = this.page.getByRole('option', { name: label });

    await expect(async () => {
      await this.localeTrigger.click();
      await expect(option).toBeVisible({ timeout: 1000 });
    }).toPass({ timeout: 15000 });

    await option.click();
  }

  async save() {
    await this.saveButton.click();
  }
}
