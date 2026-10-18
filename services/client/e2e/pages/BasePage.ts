import { type Locator, type Page, expect } from '@playwright/test';

export class BasePage {
  readonly page: Page;

  constructor(page: Page) {
    this.page = page;
  }

  getToast() {
    return this.page.locator('[data-sonner-toast]').first();
  }

  async waitForToast() {
    const toast = this.page.locator('[data-sonner-toast]').first();
    await toast.waitFor({ state: 'attached', timeout: 15000 });
    return toast;
  }

  async waitForUrl(url: string) {
    await this.page.waitForURL(url);
  }

  protected async fillField(locator: Locator, value: string) {
    await locator.click();
    await locator.fill(value);
  }

  protected async saveSettingsPane(pane: Locator) {
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

  protected pickerOverlay(title: string) {
    return this.page
      .locator('[data-slot="popover-content"]')
      .filter({ hasText: title });
  }

  protected pickerItem(overlay: Locator, name: string) {
    return overlay
      .getByRole('button')
      .filter({ has: this.page.getByText(name, { exact: true }) });
  }

  async clickUntil(
    trigger: Locator,
    target: Locator,
    beforeClick?: () => Promise<void>,
  ) {
    await expect(async () => {
      await beforeClick?.();
      await trigger.click();
      await expect(target).toBeVisible({ timeout: 1000 });
    }).toPass({ timeout: 15000 });
  }

  async gotoWithPausedClock(goto: () => Promise<void>) {
    const installedAt = Date.now();

    await this.page.clock.install({ time: installedAt });
    await goto();
    await this.page.clock.pauseAt(installedAt + 60_000);
  }

  async clickUntilOnPausedClock(trigger: Locator, target: Locator) {
    await this.clickUntil(trigger, target, () => this.page.clock.runFor(100));
  }

  viewModeOption(name: 'Grid' | 'List' | 'Table') {
    return this.page.getByRole('radio', { name });
  }

  async switchViewMode(name: 'Grid' | 'List' | 'Table') {
    const option = this.viewModeOption(name);
    await expect(async () => {
      await option.click();
      await expect(option).toHaveAttribute('aria-checked', 'true', {
        timeout: 1000,
      });
    }).toPass({ timeout: 15000 });
  }
}
