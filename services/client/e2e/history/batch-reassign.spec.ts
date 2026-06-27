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

const waitForReassign = (page: Page) =>
  page.waitForResponse(
    (response) =>
      response.url().includes('/api/images/batch') &&
      response.request().method() === 'PUT',
  );

test.describe('History batch reassign on a table page @serial', () => {
  test.describe.configure({ mode: 'serial' });

  let context: BrowserContext;
  let page: Page;
  let historyPage: HistoryPage;
  let api: ApiClient;

  test.beforeAll(async ({ browser }) => {
    const account = unique('rsntbl');
    api = await provisionUser(account);
    await seedUploads(api, 'rsntbl');

    context = await signInContext(browser, account);
    page = await context.newPage();
    historyPage = new HistoryPage(page);
    await historyPage.useTableView();
  });

  test.afterAll(async () => {
    await context?.close();
  });

  test('reassigning collections keeps the current table page', async () => {
    const collectionName = `Reassign Collection ${Date.now()}`;
    const collectionId = await api.content.createCollection({
      name: collectionName,
    });

    await historyPage.goto();
    await historyPage.tableRows
      .locator('a[href^="/info/"]')
      .first()
      .waitFor({ state: 'visible' });
    await expect(historyPage.tableRows).toHaveCount(PAGE_SIZE);

    await historyPage.goToNextTablePage();
    expect(await historyPage.tableCurrentPageNumber()).toBe(2);

    const idsOnPage = await historyPage.visibleTableImageIds();
    const selected = idsOnPage.slice(0, 3);

    await historyPage.selectTableRows(selected);
    await expect(historyPage.actionBar).toBeVisible();

    const reassign = waitForReassign(page);
    await historyPage.reassignCollection(collectionName);
    const response = await reassign;
    expect(response.ok()).toBe(true);

    expect(await historyPage.tableCurrentPageNumber()).toBe(2);

    const stillVisible = await historyPage.visibleTableImageIds();
    for (const id of selected) {
      expect(stillVisible).toContain(id);
      await expect(
        historyPage.collectionBadgeIn(
          historyPage.tableRowFor(id),
          collectionId,
        ),
      ).toBeVisible();
    }

    const itemIds = (await api.content.getCollectionItems(collectionId)).map(
      (item) => item.itemId,
    );
    for (const id of selected) {
      expect(itemIds).toContain(id);
    }
  });

  test('reassigning tags keeps the current table page', async () => {
    const tagName = `reassigntag${Date.now()}`;
    const tagId = await api.content.createTag(tagName);

    await historyPage.goto();
    await historyPage.tableRows
      .locator('a[href^="/info/"]')
      .first()
      .waitFor({ state: 'visible' });
    await expect(historyPage.tableRows).toHaveCount(PAGE_SIZE);

    await historyPage.goToNextTablePage();
    expect(await historyPage.tableCurrentPageNumber()).toBe(2);

    const idsOnPage = await historyPage.visibleTableImageIds();
    const selected = idsOnPage.slice(0, 3);

    await historyPage.selectTableRows(selected);
    await expect(historyPage.actionBar).toBeVisible();

    const reassign = waitForReassign(page);
    await historyPage.reassignTag(tagName);
    const response = await reassign;
    expect(response.ok()).toBe(true);

    expect(await historyPage.tableCurrentPageNumber()).toBe(2);

    const stillVisible = await historyPage.visibleTableImageIds();
    for (const id of selected) {
      expect(stillVisible).toContain(id);
      await expect(
        historyPage.tagBadgeIn(historyPage.tableRowFor(id), tagName),
      ).toBeVisible();
    }

    for (const id of selected) {
      expect(await api.content.getImageTagIds(id)).toContain(tagId);
    }
  });
});

test.describe('History batch reassign deduplicates an already-applied tag @serial', () => {
  test.describe.configure({ mode: 'serial' });

  let context: BrowserContext;
  let page: Page;
  let historyPage: HistoryPage;
  let api: ApiClient;
  const pageErrors: Error[] = [];

  let imageIds: string[];
  let preTaggedId: string;
  let tagName: string;
  let tagId: string;

  test.beforeAll(async ({ browser }) => {
    const account = unique('rdup');
    api = await provisionUser(account);

    imageIds = [];
    for (let index = 1; index <= 3; index++) {
      imageIds.push(
        await api.content.uploadImage({ fileName: `rdup-${index}.png` }),
      );
    }
    preTaggedId = imageIds[0];

    tagName = `rduptag${Date.now()}`;
    tagId = await api.content.createTag(tagName);
    await api.content.tagImage(preTaggedId, tagId);
    expect(await api.content.getImageTagIds(preTaggedId)).toContain(tagId);

    context = await signInContext(browser, account);
    page = await context.newPage();
    page.on('pageerror', (error) => pageErrors.push(error));
    historyPage = new HistoryPage(page);
    await historyPage.useGridView();
  });

  test.afterAll(async () => {
    await context?.close();
  });

  test('re-applying a tag to a partially tagged selection keeps a single badge and a live UI', async () => {
    await historyPage.goto();
    await expect(historyPage.gridCards).toHaveCount(3);

    await historyPage.selectImages(3);
    await expect(historyPage.actionBar).toBeVisible();

    const reassign = waitForReassign(page);
    await historyPage.reassignTag(tagName);
    const response = await reassign;
    expect(response.ok()).toBe(true);

    expect(pageErrors).toEqual([]);

    await expect(
      historyPage.tagBadgeIn(historyPage.cardFor(preTaggedId), tagName),
    ).toHaveCount(1);

    await expect(historyPage.actionBar).toBeHidden();

    await historyPage.selectImages(1);
    await expect(historyPage.actionBar).toBeVisible();

    expect(await api.content.getImageTagIds(preTaggedId)).toEqual([tagId]);
  });
});

test.describe('History batch reassign in accumulating grid view @serial', () => {
  test.describe.configure({ mode: 'serial' });

  let context: BrowserContext;
  let page: Page;
  let historyPage: HistoryPage;
  let api: ApiClient;

  test.beforeAll(async ({ browser }) => {
    const account = unique('rsngrd');
    api = await provisionUser(account);
    await seedUploads(api, 'rsngrd');

    context = await signInContext(browser, account);
    page = await context.newPage();
    historyPage = new HistoryPage(page);
    await historyPage.useGridView();
  });

  test.afterAll(async () => {
    await context?.close();
  });

  test('reassigning a collection does not collapse the accumulated list', async () => {
    const collectionName = `Grid Reassign ${Date.now()}`;
    const collectionId = await api.content.createCollection({
      name: collectionName,
    });

    await historyPage.goto();
    await historyPage.gridCards.first().waitFor({ state: 'visible' });
    await expect(historyPage.gridCards).toHaveCount(PAGE_SIZE);

    await historyPage.loadMoreButton.click();
    await expect
      .poll(() => historyPage.gridCards.count(), { timeout: 15000 })
      .toBeGreaterThan(PAGE_SIZE);

    const countBefore = await historyPage.gridCards.count();
    const selected = (await historyPage.visibleImageIds()).slice(0, 3);

    await historyPage.selectImages(3);
    await expect(historyPage.actionBar).toBeVisible();

    const reassign = waitForReassign(page);
    await historyPage.reassignCollection(collectionName);
    const response = await reassign;
    expect(response.ok()).toBe(true);

    await expect
      .poll(() => historyPage.gridCards.count(), { timeout: 15000 })
      .toBe(countBefore);

    for (const id of selected) {
      await expect(
        historyPage.collectionBadgeIn(historyPage.cardFor(id), collectionId),
      ).toBeVisible();
    }

    const itemIds = (await api.content.getCollectionItems(collectionId)).map(
      (item) => item.itemId,
    );
    for (const id of selected) {
      expect(itemIds).toContain(id);
    }
  });
});
