import { type Page } from '@playwright/test';

import { MediaFeedPage } from './MediaFeedPage';

export class BookmarksPage extends MediaFeedPage {
  static readonly URL = '/bookmarks';

  readonly unavailableText = 'Image no longer available';

  constructor(page: Page) {
    super(page);
  }

  async goto() {
    await this.page.goto(BookmarksPage.URL);
  }

  unavailableItem() {
    return this.page
      .locator('main')
      .getByText(this.unavailableText, { exact: true })
      .locator('xpath=..');
  }

  unavailableRow() {
    return this.listRows.filter({ hasText: this.unavailableText });
  }

  removeUnavailableButton() {
    return this.unavailableItem().getByRole('button', {
      name: 'Remove bookmark',
      exact: true,
    });
  }

  emptyState() {
    return this.page.getByRole('heading', { name: 'No bookmarks yet' });
  }
}
