import { type Locator, type Page, expect } from '@playwright/test';

import { BasePage } from './BasePage';

export class AdminSettingsPage extends BasePage {
  static readonly SECURITY_URL = '/admin/settings/security';

  readonly allowRegistrationSwitch = this.switchByName('allowRegistration');
  readonly approvalRequiredSwitch = this.switchByName('approvalRequired');

  readonly allowGuestUploadsSwitch = this.switchByName(
    'accessAllowGuestUploads',
  );
  readonly allowUnauthenticatedAccessSwitch = this.switchByName(
    'accessAllowUnauthenticatedAccess',
  );
  readonly requireAuthForMediaSharesSwitch = this.switchByName(
    'accessRequireAuthForMediaShares',
  );
  readonly requireAuthForCollectionSharesSwitch = this.switchByName(
    'accessRequireAuthForCollectionShares',
  );

  constructor(page: Page) {
    super(page);
  }

  get heading() {
    return this.page.getByRole('heading', { name: 'Security' });
  }

  get guestUploadWarningNotice() {
    return this.page.getByText(
      'Anyone can upload without identifying themselves',
    );
  }

  get embedNotice() {
    return this.page.getByText(
      'Keep this off if you intend to embed media on external sites',
    );
  }

  async gotoSecurity() {
    await this.page.goto(AdminSettingsPage.SECURITY_URL);
  }

  switchByName(name: string): Locator {
    return this.page.locator(
      `xpath=//input[@name="${name}"]/preceding-sibling::*[@role="switch"][1]`,
    );
  }

  async toggleSetting(name: string) {
    const toggle = this.switchByName(name);
    const before = await toggle.getAttribute('aria-checked');

    await expect(async () => {
      await toggle.click();
      expect(await toggle.getAttribute('aria-checked')).not.toBe(before);
    }).toPass({ timeout: 15000 });
  }

  async setSwitch(name: string, checked: boolean) {
    const toggle = this.switchByName(name);
    const current = await toggle.getAttribute('aria-checked');

    if (current === String(checked)) {
      return;
    }

    await this.toggleSetting(name);
  }

  async saveUserSettings() {
    await this.saveSettings(this.allowRegistrationSwitch);
  }

  async saveAccessSettings() {
    await this.saveSettings(this.allowGuestUploadsSwitch);
  }

  private settingRow(name: string): Locator {
    return this.page
      .locator('div.flex-col', { has: this.switchByName(name) })
      .last();
  }

  async resetAccessSetting(name: string) {
    const row = this.settingRow(name);
    const resetTrigger = row.getByLabel('Reset to default value');
    const confirmButton = row.getByRole('button', { name: 'Confirm' });

    await this.clickUntil(resetTrigger, confirmButton);
    await confirmButton.click();
  }

  private async saveSettings(paneSwitch: Locator) {
    const pane = this.page.locator('form').filter({ has: paneSwitch });
    const saveButton = pane.getByRole('button', { name: 'Save Changes' });

    await expect(async () => {
      const responsePromise = this.page.waitForResponse(
        (response) =>
          response.url().includes('/api/settings') &&
          response.request().method() === 'POST',
        { timeout: 1000 },
      );
      await saveButton.click();
      await responsePromise;
    }).toPass({ timeout: 15000 });
  }
}
