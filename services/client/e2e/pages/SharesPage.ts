import { type Page, expect } from '@playwright/test';

import { BasePage } from './BasePage';

export class SharesPage extends BasePage {
  static readonly URL = '/shares';

  readonly actionsTrigger = this.page.getByRole('button', {
    name: 'Share actions',
  });
  readonly emptyHeading = this.page.getByRole('heading', {
    name: 'No shares yet',
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

  async unpublishFirst() {
    const unpublishOption = this.page
      .getByRole('button', { name: 'Unpublish' })
      .first();
    await this.clickUntil(this.actionsTrigger.first(), unpublishOption);
    await unpublishOption.click();

    const confirmButton = this.page
      .getByRole('button', { name: 'Unpublish' })
      .last();
    await confirmButton.waitFor({ state: 'visible' });
    await confirmButton.click();
  }
}
