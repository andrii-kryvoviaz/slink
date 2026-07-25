import { type Page, expect } from '@playwright/test';

import { BasePage } from './BasePage';

export class CollectionsPage extends BasePage {
  static readonly URL = '/collections';

  readonly createButton = this.page.getByRole('button', {
    name: 'Create',
    exact: true,
  });
  readonly createDialog = this.page.getByRole('dialog');
  readonly nameInput = this.page.getByPlaceholder(
    'e.g., Summer Vacation, Portfolio',
  );
  readonly submitButton = this.page.getByRole('button', {
    name: 'Create Collection',
  });
  readonly gridViewButton = this.page.getByRole('radio', { name: 'Grid' });
  readonly tableViewButton = this.page.getByRole('radio', { name: 'Table' });
  readonly searchInput = this.page.getByPlaceholder('Search collections...');

  constructor(page: Page) {
    super(page);
  }

  get heading() {
    return this.page.getByRole('heading', { name: 'Collections', exact: true });
  }

  get deleteMenuItem() {
    return this.page.getByRole('menuitem', { name: 'Delete', exact: true });
  }

  get deleteConfirmationHeading() {
    return this.page.getByRole('heading', {
      name: 'Delete Collection',
      exact: true,
    });
  }

  get deleteImagesSwitch() {
    return this.page
      .locator('label', { hasText: 'Also delete images' })
      .getByRole('switch');
  }

  get confirmDeleteButton() {
    return this.page.getByRole('button', { name: 'Delete', exact: true });
  }

  rowFor(collectionId: string) {
    return this.page.getByRole('row').filter({
      has: this.page.locator(`a[href="/collection/${collectionId}"]`),
    });
  }

  actionsTrigger(collectionId: string) {
    return this.rowFor(collectionId).getByLabel('Collection actions');
  }

  async openDeleteConfirmation(collectionId: string) {
    await this.clickUntil(
      this.actionsTrigger(collectionId),
      this.deleteMenuItem,
    );
    await this.deleteMenuItem.click();
    await expect(this.deleteConfirmationHeading).toBeVisible();
  }

  async confirmDelete() {
    const deleted = this.page.waitForResponse(
      (response) =>
        response.request().method() === 'DELETE' &&
        response.url().includes('/api/collection'),
    );

    await this.confirmDeleteButton.click();
    const response = await deleted;

    expect(response.ok()).toBe(true);
  }

  async goto() {
    await this.page.goto(CollectionsPage.URL);
  }

  async createCollection(name: string, description?: string) {
    await this.clickUntil(this.createButton, this.createDialog);
    await this.nameInput.fill(name);
    if (description) {
      await this.page
        .getByPlaceholder("What's this collection about?")
        .fill(description);
    }

    const created = this.page.waitForResponse(
      (response) =>
        response.request().method() === 'POST' &&
        response.url().includes('/api/collection'),
    );

    await this.createDialog
      .getByRole('button', { name: 'Create Collection' })
      .click();
    const response = await created;

    expect(response.ok()).toBe(true);
    await expect(this.createDialog).toBeHidden();
  }

  async setViewMode(mode: 'grid' | 'table') {
    const button = mode === 'grid' ? this.gridViewButton : this.tableViewButton;
    await expect(async () => {
      await button.click();
      expect(await this.readViewMode()).toBe(mode);
    }).toPass({ timeout: 15000 });
  }

  async readViewMode(): Promise<string | null> {
    const cookies = await this.page.context().cookies();
    const match = cookies.find(
      (cookie) => cookie.name === 'settings.collections',
    );
    if (!match) {
      return null;
    }
    return JSON.parse(decodeURIComponent(match.value)).viewMode ?? null;
  }
}
