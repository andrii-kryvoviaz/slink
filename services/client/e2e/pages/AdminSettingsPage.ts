import { type Locator, type Page, expect } from '@playwright/test';

import { BasePage } from './BasePage';

export class AdminSettingsPage extends BasePage {
  static readonly SECURITY_URL = '/admin/settings/security';

  readonly allowRegistrationSwitch = this.switchByName('allowRegistration');
  readonly approvalRequiredSwitch = this.switchByName('approvalRequired');

  constructor(page: Page) {
    super(page);
  }

  get heading() {
    return this.page.getByRole('heading', { name: 'Security' });
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

  async saveUserSettings() {
    const pane = this.page
      .locator('form')
      .filter({ has: this.allowRegistrationSwitch });
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
