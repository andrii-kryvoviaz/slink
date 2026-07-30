import { type Locator, type Page, expect } from '@playwright/test';

import { BasePage } from './BasePage';

export class AdminUsersPage extends BasePage {
  static readonly URL = '/admin/user';
  static readonly PAGE_SIZE = 96;

  readonly rows = this.page.getByRole('table').locator('tbody tr');
  readonly menu = this.page.getByRole('menu');
  readonly deleteMenuItem = this.page.getByRole('menuitem', {
    name: 'Delete',
    exact: true,
  });
  readonly nextPageButton = this.page.locator('button[title="Next page"]');

  constructor(page: Page) {
    super(page);
  }

  get disableOption(): Locator {
    return this.menu.getByRole('radio', { name: 'Disable the account' });
  }

  get purgeOption(): Locator {
    return this.menu.getByRole('radio', { name: 'Disable and delete content' });
  }

  get confirmDeleteButton(): Locator {
    return this.menu.getByRole('button', { name: 'Delete User' });
  }

  get undoNotice(): Locator {
    return this.menu.getByText('This cannot be undone.');
  }

  rowFor(displayName: string): Locator {
    return this.rows.filter({ hasText: displayName });
  }

  async goto() {
    await this.page.context().addCookies([
      {
        name: 'settings.table',
        value: JSON.stringify({
          users: { pageSize: AdminUsersPage.PAGE_SIZE },
        }),
        url: process.env.E2E_BASE_URL ?? 'http://localhost:3100',
      },
    ]);

    await this.page.goto(AdminUsersPage.URL);
    await this.waitForFullList();
  }

  async waitForFullList() {
    await expect(this.rows.first()).toBeVisible({ timeout: 20000 });
    await expect(this.nextPageButton).toBeDisabled();
  }

  async findRow(displayName: string): Promise<Locator> {
    const row = this.rowFor(displayName);
    await expect(row).toBeVisible({ timeout: 20000 });

    return row;
  }

  async openDeleteConfirmation(row: Locator) {
    await this.clickUntil(
      row.getByRole('button', { name: 'Actions' }),
      this.deleteMenuItem,
    );
    await this.clickUntil(this.deleteMenuItem, this.confirmDeleteButton);
  }

  async setPurge(checked: boolean) {
    const option = checked ? this.purgeOption : this.disableOption;

    await expect(async () => {
      await option.click();
      await expect(option).toHaveAttribute('aria-checked', 'true', {
        timeout: 1000,
      });
    }).toPass({ timeout: 15000 });
  }

  async optionTexts(): Promise<string[]> {
    return [
      await this.disableOption.innerText(),
      await this.purgeOption.innerText(),
    ];
  }

  async confirmDeletion() {
    await this.confirmDeleteButton.click();
  }
}
