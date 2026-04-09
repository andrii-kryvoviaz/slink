import type { Locator, Page } from '@playwright/test';

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
}
