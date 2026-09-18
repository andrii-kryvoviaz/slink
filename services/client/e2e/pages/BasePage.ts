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
