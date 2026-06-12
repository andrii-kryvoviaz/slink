import type { BrowserContext, Page } from '@playwright/test';

import { expect, test } from '../fixtures/auth.fixture';
import { unique } from '../helpers/accounts';
import type { ApiClient } from '../helpers/api';
import { provisionUser } from '../helpers/provisioning';
import { signInContext } from '../helpers/session';
import { HistoryPage } from '../pages/HistoryPage';

const PAGE_SIZE = 12;
const TOTAL_UPLOADS = 18;

test.describe('History cursor backfill after delete', () => {
  test.describe.configure({ mode: 'serial' });

  let context: BrowserContext;
  let page: Page;
  let historyPage: HistoryPage;
  let api: ApiClient;
  let expectedVisible: string[] = [];
  let backfillQueue: string[] = [];

  const expectVisibleIds = async () => {
    await expect
      .poll(
        async () => {
          const ids = await historyPage.visibleImageIds();
          return ids.toSorted();
        },
        { timeout: 15000 },
      )
      .toEqual(expectedVisible.toSorted());
  };

  const applyDelete = (deleted: string[]) => {
    const refill = backfillQueue.splice(0, deleted.length);
    expectedVisible = expectedVisible
      .filter((id) => !deleted.includes(id))
      .concat(refill);
  };

  test.beforeAll(async ({ browser }) => {
    const account = unique('backfill');
    api = await provisionUser(account);

    for (let index = 1; index <= TOTAL_UPLOADS; index++) {
      const name = `backfill-${String(index).padStart(2, '0')}.png`;
      await api.content.uploadImage({ fileName: name });
    }

    const serverOrder = await api.content.listHistoryIds(TOTAL_UPLOADS * 2);
    expect(serverOrder).toHaveLength(TOTAL_UPLOADS);

    expectedVisible = serverOrder.slice(0, PAGE_SIZE);
    backfillQueue = serverOrder.slice(PAGE_SIZE);

    context = await signInContext(browser, account);
    page = await context.newPage();
    historyPage = new HistoryPage(page);
    await historyPage.useGridView();
  });

  test.afterAll(async () => {
    await context?.close();
  });

  test('initial load shows exactly one page of newest images', async () => {
    await historyPage.goto();
    await historyPage.gridCards.first().waitFor({ state: 'visible' });

    await expect(historyPage.gridCards).toHaveCount(PAGE_SIZE);
    await expectVisibleIds();
    await expect(historyPage.loadMoreButton).toBeVisible();
  });

  test('batch delete of three backfills the next three images', async () => {
    const visibleBefore = await historyPage.visibleImageIds();
    const deleted = visibleBefore.slice(0, 3);

    await historyPage.selectImages(3);
    await expect(historyPage.deleteButton).toBeVisible();
    await historyPage.batchDelete(3);

    for (const id of deleted) {
      await expect(historyPage.infoLink(id)).toBeHidden();
    }

    applyDelete(deleted);
    await expect(historyPage.gridCards).toHaveCount(PAGE_SIZE, {
      timeout: 15000,
    });
    await expectVisibleIds();
  });

  test('single delete backfills exactly the next image', async () => {
    const [deleted] = await historyPage.visibleImageIds();

    await historyPage.deleteSingle(deleted);

    await expect(historyPage.infoLink(deleted)).toBeHidden();
    applyDelete([deleted]);
    await expect(historyPage.gridCards).toHaveCount(PAGE_SIZE, {
      timeout: 15000,
    });
    await expectVisibleIds();
  });

  test('deleting past the remaining items only shrinks the list', async () => {
    const visibleBefore = await historyPage.visibleImageIds();
    const deleted = visibleBefore.slice(0, 3);

    await historyPage.selectImages(3);
    await expect(historyPage.deleteButton).toBeVisible();
    await historyPage.batchDelete(3);

    applyDelete(deleted);
    await expectVisibleIds();
    await expect(historyPage.loadMoreButton).toBeHidden();

    const [single] = await historyPage.visibleImageIds();
    await historyPage.deleteSingle(single);

    applyDelete([single]);
    await expectVisibleIds();
    await expect(historyPage.loadMoreButton).toBeHidden();
  });
});

test.describe('History table backfill after delete', () => {
  let context: BrowserContext;
  let page: Page;
  let historyPage: HistoryPage;
  let serverOrder: string[] = [];

  test.beforeAll(async ({ browser }) => {
    const account = unique('bftable');
    const api = await provisionUser(account);

    for (let index = 1; index <= TOTAL_UPLOADS; index++) {
      const name = `bftable-${String(index).padStart(2, '0')}.png`;
      await api.content.uploadImage({ fileName: name });
    }

    serverOrder = await api.content.listHistoryIds(TOTAL_UPLOADS * 2);
    expect(serverOrder).toHaveLength(TOTAL_UPLOADS);

    context = await signInContext(browser, account);
    page = await context.newPage();
    historyPage = new HistoryPage(page);
    await historyPage.useTableView();
  });

  test.afterAll(async () => {
    await context?.close();
  });

  test('row delete keeps remaining rows and backfills the next image', async () => {
    await historyPage.goto();
    await historyPage.tableRows
      .locator('a[href^="/info/"]')
      .first()
      .waitFor({ state: 'visible' });

    await expect(historyPage.tableRows).toHaveCount(PAGE_SIZE);
    await expect
      .poll(async () => historyPage.visibleTableImageIds(), { timeout: 15000 })
      .toEqual(serverOrder.slice(0, PAGE_SIZE));

    const visibleBefore = await historyPage.visibleTableImageIds();

    const [deleted] = visibleBefore;
    await historyPage.deleteTableRow(deleted);

    await expect(historyPage.infoLink(deleted)).toHaveCount(0, {
      timeout: 15000,
    });
    await expect(historyPage.tableRows).toHaveCount(PAGE_SIZE, {
      timeout: 15000,
    });

    const expectedRows = visibleBefore.slice(1).concat(serverOrder[PAGE_SIZE]);
    await expect
      .poll(async () => historyPage.visibleTableImageIds(), { timeout: 15000 })
      .toEqual(expectedRows);
  });
});
