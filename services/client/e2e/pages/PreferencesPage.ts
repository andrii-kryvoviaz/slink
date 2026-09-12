import { type Locator, type Page, expect } from '@playwright/test';

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

const THEME_LABELS: Record<string, string> = {
  default: 'Default',
  nord: 'Nord',
};

export class PreferencesPage extends BasePage {
  static readonly URL = '/preferences';

  readonly localeTrigger = this.page
    .locator('[data-slot="select-trigger"]')
    .first();
  readonly appearanceSection = this.page
    .locator('section')
    .filter({ has: this.page.getByRole('heading', { name: 'Appearance' }) });
  readonly themeTrigger = this.appearanceSection.locator(
    '[data-slot="select-trigger"]',
  );
  readonly autoPublishSwitch = this.page.locator(
    'xpath=//input[@name="image.externalUploadAutoPublish"]/preceding-sibling::*[@role="switch"][1]',
  );
  readonly saveButton = this.page.locator('button[type="submit"]:visible');

  readonly navigationSection = this.page
    .locator('section')
    .filter({ has: this.page.getByRole('heading', { name: 'Navigation' }) });
  readonly imageUploadsSection = this.page.locator('section').filter({
    has: this.page.getByRole('heading', { name: 'Image Uploads' }),
  });
  readonly licensingSection = this.page.locator('section').filter({
    has: this.page.getByRole('heading', { name: 'Image Licensing' }),
  });

  readonly landingPageTrigger = this.triggerForSetting('Default Landing Page');
  readonly visibilityTrigger = this.triggerForSetting('Default Visibility');
  readonly exifTrigger = this.triggerForSetting('EXIF Metadata');
  readonly licenseTrigger = this.triggerForSetting('Default License');

  readonly syncLicenseSwitch = this.licensingSection.getByRole('switch');

  constructor(page: Page) {
    super(page);
  }

  private triggerForSetting(label: string): Locator {
    return this.page.locator(
      `xpath=//h3[normalize-space()="${label}"]/ancestor::div[.//*[@data-slot="select-trigger"]][1]//*[@data-slot="select-trigger"]`,
    );
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

  async selectTheme(value: string) {
    const label = THEME_LABELS[value] ?? value;
    const option = this.page.getByRole('option', { name: label, exact: true });

    await this.clickUntil(this.themeTrigger, option);
    await option.click();
  }

  async save() {
    await this.saveButton.click();
  }

  async selectOption(trigger: Locator, label: string) {
    const option = this.page.getByRole('option', { name: label, exact: true });

    await this.clickUntil(trigger, option);
    await option.click();
    await expect(this.page.getByRole('option')).toHaveCount(0);
  }

  async pickAnyLicense(): Promise<string> {
    const anyOption = this.page
      .getByRole('option')
      .filter({ hasNotText: 'No license' })
      .first();

    await this.clickUntil(this.licenseTrigger, anyOption);
    const label = ((await anyOption.textContent()) ?? '').trim();

    await anyOption.click();
    await expect(this.page.getByRole('option')).toHaveCount(0);

    return label;
  }

  async turnSwitchOn(switchLocator: Locator) {
    await this.setSwitch(switchLocator, 'true');
  }

  async turnSwitchOff(switchLocator: Locator) {
    await this.setSwitch(switchLocator, 'false');
  }

  private async setSwitch(switchLocator: Locator, checked: 'true' | 'false') {
    await expect(async () => {
      await switchLocator.click();
      await expect(switchLocator).toHaveAttribute('aria-checked', checked, {
        timeout: 1000,
      });
    }).toPass({ timeout: 15000 });
  }
}
