import type { Locator, Page } from '@playwright/test';

import { BasePage } from './BasePage';

export class SharesPage extends BasePage {
  static readonly URL = '/shares';

  private static readonly COPIED_ICON_SELECTOR = 'svg.text-success-text';

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

  copyLinkButton(row: Locator) {
    return row.getByRole('button', { name: 'Copy link' });
  }

  shortLinkFor(row: Locator) {
    return this.copyLinkButton(row).locator('xpath=preceding-sibling::span[1]');
  }

  copiedIndicator(row: Locator) {
    return this.copyLinkButton(row).locator(SharesPage.COPIED_ICON_SELECTOR);
  }

  async expiresCell(row: Locator): Promise<Locator> {
    const headers = await this.page.getByRole('columnheader').allInnerTexts();
    const index = headers.findIndex((text) => text.trim() === 'Expires');

    return row.getByRole('cell').nth(index);
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
