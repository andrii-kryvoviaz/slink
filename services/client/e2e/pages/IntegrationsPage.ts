import { type Locator, type Page, expect } from '@playwright/test';

import { BasePage } from './BasePage';

export class IntegrationsPage extends BasePage {
  static readonly URL = '/integrations';

  readonly createButton = this.page.getByRole('button', { name: 'Create' });
  readonly nameInput = this.page.getByPlaceholder('e.g., ShareX Upload Key');
  readonly submitButton = this.page.getByRole('button', {
    name: 'Create API Key',
  });
  readonly createdKeyHeading = this.page.getByText('Important Notice');
  readonly createdKeyInput = this.page.locator('input[readonly]').first();
  readonly closeButton = this.page
    .locator('button[data-slot="button"]:not([data-dialog-close])', {
      hasText: 'Close',
    })
    .first();

  constructor(page: Page) {
    super(page);
  }

  async goto() {
    await this.page.goto(IntegrationsPage.URL);
  }

  async createApiKey(name: string): Promise<string> {
    await this.clickUntil(this.createButton, this.nameInput);
    await this.nameInput.fill(name);
    await this.submitButton.click();

    await expect(this.createdKeyHeading).toBeVisible();
    await expect(this.createdKeyInput).toBeVisible();

    const rawKey = await this.createdKeyInput.inputValue();
    return rawKey;
  }

  async closeCreatedKeyDialog() {
    await this.closeButton.click();
    await expect(this.createdKeyHeading).toBeHidden();
  }

  card(name: string): Locator {
    return this.page.locator('div.group', {
      has: this.page.getByRole('heading', { level: 5, name, exact: true }),
    });
  }

  async revoke(name: string) {
    const card = this.card(name);
    await expect(card).toBeVisible();

    const revokeTrigger = card.locator('button[title="Revoke API Key"]');
    const confirmButton = this.page.getByRole('button', { name: 'Revoke Key' });

    await this.clickUntil(revokeTrigger, confirmButton);
    await confirmButton.click();
  }
}
