import type { Page } from '@playwright/test';

import { BasePage } from './BasePage';

export class SharePage extends BasePage {
  static lockedUrl(shareId: string) {
    return `/share/locked/${shareId}`;
  }

  readonly passwordInput = this.page.locator('input[type="password"]');
  readonly continueButton = this.page.getByRole('button', { name: 'Continue' });

  constructor(page: Page) {
    super(page);
  }

  get protectedHeading() {
    return this.page.getByRole('heading', { name: 'This share is protected' });
  }

  get unavailableHeading() {
    return this.page.getByRole('heading', { name: 'Share unavailable' });
  }

  async gotoLocked(shareId: string) {
    await this.page.goto(SharePage.lockedUrl(shareId));
  }

  async unlock(password: string) {
    await this.protectedHeading.waitFor({ state: 'visible' });
    await this.page.locator('input[type="password"]').fill(password);
    await this.continueButton.click();
  }

  async expectUnavailable() {
    await this.unavailableHeading.waitFor({ state: 'visible' });
  }
}
