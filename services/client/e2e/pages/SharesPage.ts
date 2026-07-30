import type { Locator, Page } from '@playwright/test';

import { BasePage } from './BasePage';

export class SharesPage extends BasePage {
  static readonly URL = '/shares';

  readonly actionsTrigger = this.page.getByRole('button', {
    name: 'Share actions',
  });
  readonly emptyHeading = this.page.getByRole('heading', {
    name: 'No shares yet',
  });
  readonly unpublishMenuItem = this.page.getByRole('button', {
    name: 'Stop sharing this link',
  });
  readonly unpublishConfirm = this.page.getByRole('button', {
    name: 'Unpublish',
    exact: true,
  });

  constructor(page: Page) {
    super(page);
  }

  get heading() {
    return this.page.getByRole('heading', { name: 'My Shares', exact: true });
  }

  async goto() {
    await this.page.goto(SharesPage.URL);
  }

  rowByName(name: string) {
    return this.page.getByRole('row').filter({ hasText: name });
  }

  rowForImage(imageId: string) {
    return this.page.getByRole('row').filter({
      has: this.page.locator(`a[href="/info/${imageId}"]`),
    });
  }

  async unpublishRow(row: Locator) {
    await this.clickUntil(
      row.getByRole('button', { name: 'Share actions' }).first(),
      this.unpublishMenuItem,
    );
    await this.unpublishMenuItem.click();
    await this.unpublishConfirm.click();
  }
}
