import { type Page, expect } from '@playwright/test';

import { BasePage } from './BasePage';

export class ImageInfoPage extends BasePage {
  readonly visibilityButton = this.page.locator(
    'button[aria-label="Make public"], button[aria-label="Make private"]',
  );
  readonly deleteTrigger = this.page.locator(
    'button[aria-label="Delete image"]',
  );
  readonly confirmDeleteButton = this.page.getByRole('button', {
    name: 'Delete Image',
    exact: true,
  });
  readonly collectionTrigger = this.page.locator(
    'button[aria-label="Add to collection"]',
  );
  readonly tagTrigger = this.page.locator('button[aria-label="Manage tags"]');
  readonly descriptionEditTrigger = this.page.getByRole('button', {
    name: 'Click to add a description...',
  });
  readonly descriptionTextarea = this.page.getByPlaceholder(
    'Add a description...',
  );
  readonly descriptionSaveButton = this.page.getByRole('button', {
    name: 'Save',
    exact: true,
  });
  readonly shareLinkInput = this.page.locator('input[readonly]');
  readonly copyShareLinkButton = this.page.getByRole('button', {
    name: 'Copy',
    exact: true,
  });
  readonly copiedShareLinkButton = this.page.getByRole('button', {
    name: 'Copied',
    exact: true,
  });

  constructor(page: Page) {
    super(page);
  }

  async goto(imageId: string) {
    await this.page.goto(`/info/${imageId}`);
  }

  async toggleVisibility() {
    await this.visibilityButton.waitFor({ state: 'visible' });
    const before = await this.visibilityButton.getAttribute('aria-pressed');

    await expect(async () => {
      await this.visibilityButton.click();
      await expect(this.visibilityButton).not.toHaveAttribute(
        'aria-pressed',
        before ?? '',
        { timeout: 1000 },
      );
    }).toPass({ timeout: 15000 });
  }

  async saveDescription(text: string) {
    await this.clickUntil(
      this.descriptionEditTrigger,
      this.descriptionTextarea,
    );
    await this.descriptionTextarea.fill(text);
    await this.descriptionSaveButton.click();
  }

  async deleteImage() {
    await this.clickUntil(this.deleteTrigger, this.confirmDeleteButton);
    await this.confirmDeleteButton.click();
  }

  get tagPicker() {
    return this.pickerOverlay('Manage tags');
  }

  get tagSearchInput() {
    return this.tagPicker.getByPlaceholder('Search');
  }

  tagPickerItem(name: string) {
    return this.pickerItem(this.tagPicker, name);
  }

  tagLink(name: string) {
    return this.page
      .locator('a[href*="tagIds="]')
      .and(this.page.getByRole('link', { name, exact: true }));
  }

  async openTagPicker() {
    await this.clickUntil(this.tagTrigger, this.tagPicker);
  }

  async searchTags(term: string) {
    await this.openTagPicker();
    await expect(this.tagSearchInput).toBeVisible();
    await this.tagSearchInput.fill(term);
  }

  async addToCollection(collectionName: string) {
    const option = this.page.getByRole('button', { name: collectionName });
    await this.clickUntil(this.collectionTrigger, option.first());
    await option.first().click();
  }

  get collectionPicker() {
    return this.page
      .locator('[data-slot="popover-content"]')
      .filter({ hasText: 'Add to collection' });
  }

  get collectionSearchInput() {
    return this.collectionPicker.getByPlaceholder('Search');
  }

  collectionPickerItem(name: string) {
    return this.collectionPicker.getByRole('button', { name, exact: true });
  }

  get collectionLinks() {
    return this.page.locator('a[href^="/collection/"]');
  }

  collectionLink(collectionId: string) {
    return this.page.locator(`a[href="/collection/${collectionId}"]`);
  }

  async openCollectionPicker() {
    await this.clickUntil(this.collectionTrigger, this.collectionSearchInput);
  }

  async searchCollections(term: string) {
    if (await this.collectionSearchInput.isHidden()) {
      await this.openCollectionPicker();
    }

    await this.collectionSearchInput.fill(term);
  }
}
