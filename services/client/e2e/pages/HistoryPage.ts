import { type Page, expect } from '@playwright/test';

import { BasePage } from './BasePage';

export class HistoryPage extends BasePage {
  static readonly URL = '/history';

  readonly gridCards = this.page.locator('article[role="button"]');
  readonly selectCheckboxes = this.page.getByRole('button', {
    name: 'Select image',
  });
  readonly deselectCheckboxes = this.page.getByRole('button', {
    name: 'Deselect image',
  });
  readonly actionBar = this.page
    .locator('div')
    .filter({
      has: this.page.getByRole('button', {
        name: /^(Select all|Deselect all)$/,
      }),
    })
    .last();

  constructor(page: Page) {
    super(page);
  }

  get heading() {
    return this.page.getByRole('heading', { name: 'Upload History' });
  }

  get deleteButton() {
    return this.actionBar
      .getByRole('button', { name: 'Delete', exact: true })
      .first();
  }

  async goto() {
    await this.page.goto(HistoryPage.URL);
  }

  async useGridView() {
    await this.page.context().addCookies([
      {
        name: 'settings.history',
        value: JSON.stringify({ viewMode: 'grid' }),
        url: process.env.E2E_BASE_URL ?? 'http://localhost:3100',
      },
    ]);
  }

  async selectImages(count: number) {
    await this.gridCards.first().waitFor({ state: 'visible' });
    for (let index = 0; index < count; index++) {
      const card = this.gridCards.nth(index);
      const checkbox = card.getByRole('button', {
        name: /^(Select image|Deselect image)$/,
      });

      await expect(async () => {
        await card.click();
        await expect(checkbox).toHaveAttribute('aria-label', 'Deselect image', {
          timeout: 1000,
        });
      }).toPass({ timeout: 15000 });
    }
  }

  confirmDeleteButton(count: number) {
    const label = count === 1 ? 'Delete Image' : 'Delete Images';
    return this.page.getByRole('button', { name: label });
  }

  async batchDelete(count: number) {
    await this.deleteButton.click();
    const confirm = this.confirmDeleteButton(count);
    await confirm.waitFor({ state: 'visible' });
    await confirm.click();
  }

  infoLink(id: string) {
    return this.page.locator(`a[href="/info/${id}"]`);
  }

  cardFor(id: string) {
    return this.infoLink(id).locator(
      'xpath=ancestor::article[@role="button"][1]',
    );
  }

  async deleteSingle(id: string) {
    const card = this.cardFor(id);
    await card.waitFor({ state: 'visible' });
    await card.hover();

    const trigger = card.locator('button[aria-label="Delete image"]');
    const confirm = this.page
      .getByRole('button', { name: 'Delete Image', exact: true })
      .last();

    await expect(async () => {
      await trigger.click();
      await expect(confirm).toBeVisible({ timeout: 1000 });
    }).toPass({ timeout: 15000 });

    await confirm.click();
  }

  viewModeOption(name: 'Grid' | 'List' | 'Table') {
    return this.page.getByRole('radio', { name });
  }

  async switchViewMode(name: 'Grid' | 'List' | 'Table') {
    const option = this.viewModeOption(name);
    await expect(async () => {
      await option.click();
      await expect(option).toHaveAttribute('aria-checked', 'true', {
        timeout: 1000,
      });
    }).toPass({ timeout: 15000 });
  }
}
