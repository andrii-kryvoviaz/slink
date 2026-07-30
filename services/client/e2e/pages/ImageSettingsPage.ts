import {
  type Locator,
  type Page,
  type Response,
  expect,
} from '@playwright/test';

import { BasePage } from './BasePage';

export class ImageSettingsPage extends BasePage {
  static readonly URL = '/admin/settings/image';

  readonly maxSizeInput = this.page.locator('input[name="imageMaxSize"]');

  constructor(page: Page) {
    super(page);
  }

  get form(): Locator {
    return this.page.locator('form').filter({ has: this.maxSizeInput });
  }

  get saveButton(): Locator {
    return this.form.getByRole('button', { name: 'Save Changes' });
  }

  get maxSizeItem(): Locator {
    return this.page
      .locator('div.relative.flex-col', { has: this.maxSizeInput })
      .last();
  }

  async goto() {
    await this.page.goto(ImageSettingsPage.URL);
    await expect(this.maxSizeInput).toBeVisible();
  }

  async fillMaxSize(amount: string) {
    const resetTrigger = this.maxSizeItem.getByLabel('Reset to default value');

    await expect(async () => {
      await this.maxSizeInput.fill(amount);
      await expect(resetTrigger).toHaveCSS('pointer-events', 'auto', {
        timeout: 1000,
      });
    }).toPass({ timeout: 15000 });
  }

  async save() {
    let saved: Response | undefined;

    await expect(async () => {
      const response = this.page.waitForResponse(
        (candidate) =>
          candidate.url().includes('/api/settings') &&
          candidate.request().method() === 'POST',
        { timeout: 2000 },
      );
      await this.saveButton.click();
      saved = await response;
    }).toPass({ timeout: 15000 });

    expect(saved?.ok()).toBe(true);
  }
}
