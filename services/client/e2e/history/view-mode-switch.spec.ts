import type { BrowserContext, Page } from '@playwright/test';

import { expect, test } from '../fixtures/auth.fixture';
import { unique } from '../helpers/accounts';
import type { ApiClient } from '../helpers/api';
import { provisionUser } from '../helpers/provisioning';
import { signInContext } from '../helpers/session';
import { HistoryPage } from '../pages/HistoryPage';

const PAGE_SIZE = 12;
const TOTAL_UPLOADS = 18;

const seedUploads = async (api: ApiClient, prefix: string) => {
  for (let index = 1; index <= TOTAL_UPLOADS; index++) {
    const name = `${prefix}-${String(index).padStart(2, '0')}.png`;
    await api.content.uploadImage({ fileName: name });
  }
};

test.describe('History view mode switch resets pagination @serial', () => {
  test.describe.configure({ mode: 'serial' });

  let context: BrowserContext;
  let page: Page;
  let historyPage: HistoryPage;
  let api: ApiClient;

  test.beforeAll(async ({ browser }) => {
    const account = unique('vmsw');
    api = await provisionUser(account);
    await seedUploads(api, 'vmsw');

    context = await signInContext(browser, account);
    page = await context.newPage();
    historyPage = new HistoryPage(page);
  });

  test.afterAll(async () => {
    await context?.close();
  });

  test('grid accumulated -> table resets to page 1', async () => {
    await historyPage.useGridView();
    await historyPage.goto();

    await historyPage.gridCards.first().waitFor({ state: 'visible' });
    await expect(historyPage.gridCards).toHaveCount(PAGE_SIZE);

    await historyPage.loadMoreButton.click();
    await expect
      .poll(() => historyPage.gridCards.count(), { timeout: 15000 })
      .toBeGreaterThan(PAGE_SIZE);

    await historyPage.switchViewMode('Table');

    await expect(historyPage.tableRows).toHaveCount(PAGE_SIZE);
    expect(await historyPage.tableCurrentPageNumber()).toBe(1);
  });

  test('table page 2 -> grid resets to page 1', async () => {
    await historyPage.useTableView();
    await historyPage.goto();

    await historyPage.tableRows
      .locator('a[href^="/info/"]')
      .first()
      .waitFor({ state: 'visible' });
    await expect(historyPage.tableRows).toHaveCount(PAGE_SIZE);

    const [firstPageFirstId] = await historyPage.visibleTableImageIds();

    await historyPage.goToNextTablePage();
    expect(await historyPage.tableCurrentPageNumber()).toBe(2);

    await historyPage.switchViewMode('Grid');

    await historyPage.gridCards.first().waitFor({ state: 'visible' });
    await expect
      .poll(async () => (await historyPage.visibleImageIds())[0], {
        timeout: 15000,
      })
      .toBe(firstPageFirstId);
  });
});
