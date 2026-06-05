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

    const submit = this.createDialog.getByRole('button', {
      name: 'Create Collection',
    });
    await expect(async () => {
      await submit.click();
      await expect(this.createDialog).toBeHidden({ timeout: 1000 });
    }).toPass({ timeout: 15000 });
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
