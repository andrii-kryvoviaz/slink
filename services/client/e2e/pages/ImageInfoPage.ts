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

  async addToCollection(collectionName: string) {
    const option = this.page.getByRole('button', { name: collectionName });
    await this.clickUntil(this.collectionTrigger, option.first());
    await option.first().click();
  }
}
