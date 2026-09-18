import { type Locator, type Page, expect } from '@playwright/test';

import { BasePage } from './BasePage';

const EMPTY_STATE_HEADINGS = {
  'no-results': 'No images found',
  'nothing-shared': 'Nothing shared yet',
} as const;

export class ExplorePage extends BasePage {
  static readonly URL = '/explore';

  readonly searchInput = this.page.getByPlaceholder(/Search/);
  readonly searchOptionsTrigger = this.page.getByRole('button', {
    name: 'Search options',
  });
  readonly clearSearchButton = this.page.getByRole('button', {
    name: 'Clear',
    exact: true,
  });
  readonly emptyStateClearButton = this.page.getByRole('button', {
    name: 'Clear search',
    exact: true,
  });
  readonly strayViewModeListbox = this.page.locator(
    'main [role="listbox"], main [aria-haspopup="listbox"]',
  );
  readonly feedItems = this.page.locator('main [role="button"][tabindex="0"]');
  readonly listRows = this.page.locator('main ul[role="list"] > li');
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

    const responsePromise = this.page.waitForResponse(
      (response) =>
        response.url().includes('/api/image') &&
        response.url().includes(encodeURIComponent(term)),
      { timeout: 15000 },
    );
    await input.click();
    await input.fill(term);
    await input.press('Enter');
    await responsePromise;
  }

  async selectSearchBy(option: 'User' | 'Description' | 'Hashtag') {
    await this.searchOptionsTrigger.click();
    const item = this.page.getByRole('menuitem', {
      name: `Search by ${option}`,
    });
    await item.waitFor({ state: 'visible' });
    await item.click();
  }

  emptyState(kind: keyof typeof EMPTY_STATE_HEADINGS) {
    return this.page.getByRole('heading', {
      name: EMPTY_STATE_HEADINGS[kind],
    });
  }

  cardFor(imageId: string) {
    return this.feedItems.filter({
      has: this.page.locator(`img[src*="${imageId}"]`),
    });
  }

  rowFor(imageId: string) {
    return this.listRows.filter({
      has: this.page.locator(`img[src*="${imageId}"]`),
    });
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

  async pressArrow(
    direction: 'ArrowRight' | 'ArrowLeft' | 'ArrowDown' | 'ArrowUp',
  ) {
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
