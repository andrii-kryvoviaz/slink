import type { Browser, BrowserContext, Page } from '@playwright/test';

import { expect, test } from '../fixtures/auth.fixture';
import { type Account, unique } from '../helpers/accounts';
import type { ApiClient } from '../helpers/api';
import { FILLER_PATTERN, seedCollectionsAhead } from '../helpers/collections';
import { provisionUser } from '../helpers/provisioning';
import { signInContext } from '../helpers/session';
import { HistoryPage } from '../pages/HistoryPage';

const TARGET_COLLECTION = 'Harbor Archive';

type Scene = {
  page: Page;
  historyPage: HistoryPage;
  imageId: string;
};

const waitForReassign = (page: Page) =>
  page.waitForResponse(
    (response) =>
      response.url().includes('/api/images/batch') &&
      response.request().method() === 'PUT',
  );

const collectionItemIds = async (api: ApiClient, collectionId: string) =>
  (await api.content.getCollectionItems(collectionId)).map(
    (item) => item.itemId,
  );

test.describe('History collection picker search', { tag: '@sc-1000' }, () => {
  let context: BrowserContext | undefined;
  let account: Account;
  let api: ApiClient;
  let targetId: string;
  let olderId: string;

  test.beforeAll(async () => {
    account = unique('colsrch');
    api = await provisionUser(account);
    ({ targetId, olderId } = await seedCollectionsAhead(
      api,
      TARGET_COLLECTION,
    ));
  });

  const openScene = async (browser: Browser): Promise<Scene> => {
    const imageId = await api.content.uploadImage();

    context = await signInContext(browser, account);
    const page = await context.newPage();
    const historyPage = new HistoryPage(page);

    return { page, historyPage, imageId };
  };

  test.afterEach(async () => {
    await context?.close();
    context = undefined;
  });

  test('adds the selected image to a collection found past the first fifty', async ({
    browser,
  }) => {
    const { page, historyPage, imageId } = await openScene(browser);
    await historyPage.useGridView();
    await historyPage.goto();
    await expect
      .poll(async () => (await historyPage.visibleImageIds())[0])
      .toBe(imageId);
    await historyPage.selectImages(1);
    await expect(historyPage.actionBar).toBeVisible();

    await historyPage.openCollectionPicker();
    await expect(
      historyPage.collectionPicker
        .getByRole('button', { name: FILLER_PATTERN })
        .first(),
    ).toBeVisible();
    await expect(
      historyPage.collectionPickerItem(TARGET_COLLECTION),
    ).toHaveCount(0);

    await historyPage.searchCollectionPicker(TARGET_COLLECTION);

    const row = historyPage.collectionPickerItem(TARGET_COLLECTION);
    await expect(row).toBeVisible();
    await expect(row).toHaveAttribute('aria-pressed', 'false');
    await expect(
      historyPage.collectionPicker.getByText('No matches found'),
    ).toHaveCount(0);

    await row.click();

    const reassign = waitForReassign(page);
    await historyPage.applyCollectionPicker();
    expect((await reassign).ok()).toBe(true);

    expect(await collectionItemIds(api, targetId)).toContain(imageId);
  });

  test('lists every collection of the image in the history row after adding a searched one from the row menu', async ({
    browser,
  }) => {
    const { historyPage, imageId } = await openScene(browser);
    await api.content.addImageToCollection(olderId, imageId);

    await historyPage.useTableView();
    await historyPage.goto();
    const tableRow = historyPage.tableRowFor(imageId);
    await expect(
      historyPage.collectionBadgeIn(tableRow, olderId),
    ).toBeVisible();

    await historyPage.searchRowCollections(imageId, TARGET_COLLECTION);

    const row = historyPage.collectionPickerItem(TARGET_COLLECTION);
    await expect(row).toBeVisible();
    await expect(row).toHaveAttribute('aria-pressed', 'false');

    await row.click();

    await expect
      .poll(() => collectionItemIds(api, targetId), { timeout: 15000 })
      .toContain(imageId);
    await expect(
      historyPage.collectionBadgeIn(tableRow, targetId),
    ).toBeVisible();
    await expect(
      historyPage.collectionBadgeIn(tableRow, olderId),
    ).toBeVisible();
    await expect(tableRow.locator('a[href^="/collection/"]')).toHaveCount(2);
  });
});
