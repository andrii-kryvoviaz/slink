import type { Locator, Page } from '@playwright/test';

import { BasePage } from './BasePage';

export class NotificationsPage extends BasePage {
  static readonly URL = '/notifications';

  readonly heading = this.page.getByRole('heading', { name: 'Notifications' });
  readonly unreadSubtitle = this.page.getByText(/\d+ unread/);
  readonly markAllReadButton = this.page.getByRole('button', {
    name: 'Mark all read',
  });
  readonly markReadButtons = this.page.getByRole('button', {
    name: 'Mark as read',
  });
  readonly emptyHeading = this.page.getByRole('heading', {
    name: 'All caught up',
  });
  readonly filteredEmptyHeading = this.page.getByRole('heading', {
    name: 'Nothing here',
  });
  readonly filterGroup = this.page.getByRole('radiogroup', {
    name: 'Filter notifications',
  });
  readonly loadMoreButton = this.page.getByRole('button', {
    name: 'Load More',
  });

  constructor(page: Page) {
    super(page);
  }

  async goto() {
    await this.page.goto(NotificationsPage.URL);
  }

  async reload() {
    const counted = this.page.waitForResponse(
      (response) =>
        new URL(response.url()).pathname.endsWith(
          '/notifications/unread-count',
        ) && response.ok(),
    );
    await this.page.reload();
    await counted;
  }

  filterChip(label: string) {
    return this.filterGroup.getByRole('radio', {
      name: new RegExp(`${label}$`),
    });
  }

  waitForList() {
    return this.page.waitForResponse(
      (response) =>
        new URL(response.url()).pathname.endsWith('/notifications') &&
        response.request().method() === 'GET',
    );
  }

  async selectFilter(label: string): Promise<URLSearchParams> {
    const listed = this.waitForList();
    await this.filterChip(label).click();

    return new URL((await listed).url()).searchParams;
  }

  entryByText(text: string | RegExp) {
    return this.page
      .getByRole('main')
      .getByRole('paragraph')
      .filter({ hasText: text })
      .locator('..');
  }

  entrySentence(entry: Locator) {
    return entry.getByRole('paragraph');
  }

  openPostButton(entry: Locator) {
    return entry.getByRole('button', { name: 'Open post' });
  }

  markReadButton(entry: Locator) {
    return entry.getByRole('button', { name: 'Mark as read' });
  }

  threadToggle(entry: Locator) {
    return entry.getByRole('button', { name: /earlier repl/ });
  }

  commentThreadToggle(entry: Locator) {
    return entry.getByRole('button', { name: /earlier comment/ });
  }

  hideEarlierButton(entry: Locator) {
    return entry.getByRole('button', {
      name: /^Hide earlier (reply|replies|comment|comments)$/,
    });
  }

  showAllButton(entry: Locator) {
    return entry.getByRole('button', { name: /^Show all \d+$/ });
  }

  showLessButton(entry: Locator) {
    return entry.getByRole('button', { name: 'Show less' });
  }

  threadRow(entry: Locator, author: string) {
    return entry
      .getByRole('button', { name: author, exact: true })
      .locator('xpath=../..');
  }

  visibleTimes(entry: Locator) {
    return entry.locator('time:visible');
  }
}
