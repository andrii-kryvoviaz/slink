import { type Locator, type Page, expect } from '@playwright/test';

import { BasePage } from './BasePage';

const MAX_PAGES_TO_WALK = 25;

export class AdminUsersPage extends BasePage {
  static readonly URL = '/admin/user';
  static readonly PAGE_SIZE = 96;

  readonly heading = this.page.getByRole('heading', {
    name: 'Users',
    exact: true,
  });
  readonly listViewButton = this.page.getByRole('radio', { name: 'List' });
  readonly pageSizeButton = this.page.getByRole('button', {
    name: /^Limit \d+$/,
  });
  readonly previousPageButton = this.page.locator(
    'button[title="Previous page"]',
  );
  readonly nextPageButton = this.page.locator('button[title="Next page"]');
  readonly currentPageButton = this.page.locator(
    'button[aria-label^="Current page "]',
  );
  readonly rows = this.page.getByRole('table').locator('tbody tr');
  readonly usernameHeader = this.page.getByRole('columnheader', {
    name: 'Username',
    exact: true,
  });
  readonly menu = this.page.getByRole('menu');
  readonly deleteMenuItem = this.page.getByRole('menuitem', {
    name: 'Delete',
    exact: true,
  });

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
    await expect(this.heading).toBeVisible();
    await this.useListView();
    await this.waitForLoadedList();
  }

  async waitForLoadedList() {
    await expect(this.usernameHeader).toBeVisible({ timeout: 20000 });
  }

  async useListView() {
    await expect(async () => {
      await this.listViewButton.click();
      await expect(this.listViewButton).toHaveAttribute('aria-checked', 'true');
    }).toPass({ timeout: 15000 });
  }

  async useMaxPageSize() {
    await expect(this.pageSizeButton).toBeVisible({ timeout: 15000 });

    const label = `Limit ${AdminUsersPage.PAGE_SIZE}`;
    if ((await this.pageSizeButton.innerText()).trim() === label) {
      return;
    }

    const option = this.page.getByRole('option', {
      name: String(AdminUsersPage.PAGE_SIZE),
      exact: true,
    });
    await this.clickUntil(this.pageSizeButton, option);

    const listed = this.page.waitForResponse(
      (response) =>
        response.url().includes('/users/') &&
        response.url().includes(`limit=${AdminUsersPage.PAGE_SIZE}`),
    );
    await option.click();
    const { data } = await (await listed).json();

    await expect(this.pageSizeButton).toHaveText(label, { timeout: 15000 });
    await expect(this.rows).toHaveCount(data.length, { timeout: 15000 });
  }

  async waitForFullList() {
    await this.waitForLoadedList();
    await expect(this.rows.first()).toBeVisible({ timeout: 20000 });
    await expect(this.nextPageButton).toBeDisabled();
  }

  async rowByUsername(username: string): Promise<Locator> {
    await this.useMaxPageSize();
    await this.goToFirstPage();

    const row = this.rowFor(username);

    for (let visited = 0; visited < MAX_PAGES_TO_WALK; visited++) {
      await expect(this.rows.first()).toBeVisible({ timeout: 15000 });

      if (await row.count()) {
        return row;
      }

      const [current, total] = await this.pageIndicator();
      if (current >= total) {
        throw new Error(
          `user ${username} is not listed on any of the ${total} pages of /admin/user`,
        );
      }

      await this.goToNextPage();
    }

    throw new Error(
      `user ${username} is not listed within the first ${MAX_PAGES_TO_WALK} pages of /admin/user`,
    );
  }

  private async pageIndicator(): Promise<[number, number]> {
    const current = Number((await this.currentPageButton.innerText()).trim());
    const totalLabel = this.currentPageButton.locator(
      'xpath=following-sibling::span[last()]',
    );

    if (!(await totalLabel.count())) {
      return [current, current];
    }

    return [current, Number((await totalLabel.innerText()).trim())];
  }

  private async goToFirstPage() {
    for (let visited = 0; visited < MAX_PAGES_TO_WALK; visited++) {
      const current = (await this.currentPageButton.innerText()).trim();
      if (current === '1') {
        return;
      }

      await this.previousPageButton.click();
      await expect(this.currentPageButton).not.toHaveText(current, {
        timeout: 15000,
      });
    }
  }

  private async goToNextPage() {
    const current = (await this.currentPageButton.innerText()).trim();

    await this.nextPageButton.click();
    await expect(this.currentPageButton).not.toHaveText(current, {
      timeout: 15000,
    });
  }

  private actionsTrigger(row: Locator): Locator {
    return row.getByRole('button', { name: 'Actions' });
  }

  async badgeInRow(username: string, label: string): Promise<Locator> {
    const row = await this.rowByUsername(username);
    return row.getByText(label, { exact: true });
  }

  async runAction(
    username: string,
    action: 'Suspend' | 'Activate' | 'Make Admin' | 'Remove Admin',
  ) {
    const row = await this.rowByUsername(username);

    const item = this.page.getByRole('menuitem', { name: action, exact: true });
    await this.clickUntil(this.actionsTrigger(row), item);
    await item.click();
    await expect(item).toBeHidden({ timeout: 15000 });
  }

  async openDeleteConfirmation(row: Locator) {
    await this.clickUntil(this.actionsTrigger(row), this.deleteMenuItem);
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
