import { type Page, expect } from '@playwright/test';

import { BasePage } from './BasePage';

export class MediaFeedPage extends BasePage {
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

  mediaFor(imageId: string) {
    return this.page.locator(`main img[src*="${imageId}"]`).first();
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

  async closeViewer() {
    await this.viewerClose.click();
    await this.viewer.waitFor({ state: 'hidden' });
  }
}
