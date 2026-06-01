import { type Locator, type Page, expect } from '@playwright/test';

import { BasePage } from './BasePage';

export class ExplorePage extends BasePage {
  static readonly URL = '/explore';

  readonly searchTrigger = this.page.getByRole('button', { name: 'Search' });
  readonly searchInput = this.page.getByPlaceholder(/Search/);
  readonly searchOptionsTrigger = this.page.getByRole('button', {
    name: 'Search options',
  });
  readonly feedItems = this.page.locator('main [role="button"][tabindex="0"]');
  readonly viewer = this.page.getByRole('dialog');
  readonly viewerClose = this.page.getByRole('button', {
    name: 'Close viewer',
  });
  readonly viewerNext = this.page.getByRole('button', { name: 'Next post' });
  readonly viewerPrev = this.page.getByRole('button', {
    name: 'Previous post',
  });

  constructor(page: Page) {
    super(page);
  }

  async goto() {
    await this.page.goto(ExplorePage.URL);
  }

  async search(term: string) {
    const input = this.searchInput.first();
    await input.waitFor({ state: 'visible' });

    await expect(async () => {
      const responsePromise = this.page.waitForResponse(
        (response) =>
          response.url().includes('/api/image') &&
          response.url().includes(encodeURIComponent(term)),
        { timeout: 1000 },
      );
      await input.click();
      await input.fill(term);
      await input.press('Enter');
      await responsePromise;
    }).toPass({ timeout: 15000 });
  }

  async selectSearchBy(option: 'User' | 'Description' | 'Hashtag') {
    await this.searchOptionsTrigger.click();
    const item = this.page.getByRole('menuitem', {
      name: `Search by ${option}`,
    });
    await item.waitFor({ state: 'visible' });
    await item.click();
  }

  async openFirstItem() {
    const first = this.feedItems.first();
    await first.waitFor({ state: 'visible' });

    await expect(async () => {
      await first.click();
      await expect(this.viewer).toBeVisible({ timeout: 1000 });
    }).toPass({ timeout: 15000 });
  }

  currentPost() {
    return new URL(this.page.url()).searchParams.get('post');
  }

  async nextItem() {
    const before = this.currentPost();
    await expect(async () => {
      await this.viewerNext.click();
      expect(this.currentPost()).not.toBe(before);
    }).toPass({ timeout: 15000 });
  }

  async prevItem() {
    const before = this.currentPost();
    await expect(async () => {
      await this.viewerPrev.click();
      expect(this.currentPost()).not.toBe(before);
    }).toPass({ timeout: 15000 });
  }

  async pressArrow(direction: 'ArrowRight' | 'ArrowLeft') {
    const before = this.currentPost();
    await expect(async () => {
      await this.page.keyboard.press(direction);
      expect(this.currentPost()).not.toBe(before);
    }).toPass({ timeout: 15000 });
  }

  async closeViewer() {
    await this.viewerClose.click();
    await this.viewer.waitFor({ state: 'hidden' });
  }

  bookmarkButton(name: 'Save' | 'Remove bookmark' = 'Save') {
    return this.page.getByRole('button', { name }).first();
  }

  async toggleBookmark(button: Locator, expected: 'true' | 'false') {
    await expect(async () => {
      await button.click();
      await expect(button).toHaveAttribute('aria-pressed', expected, {
        timeout: 1000,
      });
    }).toPass({ timeout: 15000 });
  }
}
