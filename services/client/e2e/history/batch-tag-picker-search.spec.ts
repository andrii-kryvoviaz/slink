import type { Browser, BrowserContext, Page, Request } from '@playwright/test';

import { expect, test } from '../fixtures/auth.fixture';
import { unique } from '../helpers/accounts';
import type { ApiClient } from '../helpers/api';
import { provisionUser } from '../helpers/provisioning';
import { signInContext } from '../helpers/session';
import { fillerTagName, seedTags } from '../helpers/tags';
import { HistoryPage } from '../pages/HistoryPage';

const TARGET_TAG = 'tv';
const FEW_FILLERS = 3;
const PAST_FIRST_PAGE_FILLERS = 55;

type Scene = {
  api: ApiClient;
  page: Page;
  historyPage: HistoryPage;
  imageId: string;
  targetTagId: string;
};

const searchTermOf = (request: Request) =>
  new URL(request.url()).searchParams.get('searchTerm');

const isTagListUrl = (url: URL) => url.pathname === '/api/tags';

const isTagList = (request: Request) =>
  request.method() === 'GET' && isTagListUrl(new URL(request.url()));

const isTagSearchFor = (term: string) => (request: Request) =>
  isTagList(request) && searchTermOf(request) === term;

const captureTagListTerms = (page: Page) => {
  const terms: string[] = [];
  page.on('request', (request) => {
    if (!isTagList(request)) return;
    terms.push(searchTermOf(request) ?? '');
  });

  return terms;
};

const settledRequest = (page: Page, predicate: (request: Request) => boolean) =>
  Promise.race([
    page.waitForEvent('requestfinished', { predicate }),
    page.waitForEvent('requestfailed', { predicate }),
  ]).catch(() => undefined);

const flushRendering = (page: Page) =>
  page.evaluate(
    () =>
      new Promise<void>((resolve) =>
        requestAnimationFrame(() =>
          requestAnimationFrame(() => setTimeout(resolve, 0)),
        ),
      ),
  );

const waitForReassign = (page: Page) =>
  page.waitForResponse(
    (response) =>
      response.url().includes('/api/images/batch') &&
      response.request().method() === 'PUT',
  );

test.describe('History batch tag picker search', { tag: '@sc-997' }, () => {
  let context: BrowserContext | undefined;

  const openScene = async (
    browser: Browser,
    fillerCount: number,
  ): Promise<Scene> => {
    const account = unique('tagsrch');
    const api = await provisionUser(account);
    const imageId = await api.content.uploadImage();
    const targetTagId = await seedTags(api, fillerCount, TARGET_TAG);

    context = await signInContext(browser, account);
    const page = await context.newPage();
    const historyPage = new HistoryPage(page);
    await historyPage.useGridView();

    return { api, page, historyPage, imageId, targetTagId };
  };

  const selectOnlyImage = async (historyPage: HistoryPage) => {
    await historyPage.goto();
    await expect(historyPage.gridCards).toHaveCount(1);
    await historyPage.selectImages(1);
    await expect(historyPage.actionBar).toBeVisible();
  };

  test.afterEach(async () => {
    await context?.close();
    context = undefined;
  });

  test('finds an existing tag when the user has a few tags', async ({
    browser,
  }) => {
    const { historyPage } = await openScene(browser, FEW_FILLERS);
    await selectOnlyImage(historyPage);

    await historyPage.searchTagPicker(TARGET_TAG);

    await expect(historyPage.tagPickerItem(TARGET_TAG)).toBeVisible();
    await expect(
      historyPage.tagPicker.getByText('No matches found'),
    ).toHaveCount(0);
    await expect(historyPage.tagPickerSearchInput).toBeVisible();
    await expect(historyPage.tagPickerSearchInput).toHaveValue(TARGET_TAG);
  });

  test('finds an existing tag that sorts after the first fifty tags', async ({
    browser,
  }) => {
    const { historyPage } = await openScene(browser, PAST_FIRST_PAGE_FILLERS);
    await selectOnlyImage(historyPage);

    await historyPage.searchTagPicker(TARGET_TAG);

    await expect(historyPage.tagPickerItem(TARGET_TAG)).toBeVisible();
    await expect(
      historyPage.tagPicker.getByText('No matches found'),
    ).toHaveCount(0);
  });

  test('applies a searched tag after the search is cleared', async ({
    browser,
  }) => {
    const { api, page, historyPage, imageId, targetTagId } = await openScene(
      browser,
      PAST_FIRST_PAGE_FILLERS,
    );
    await selectOnlyImage(historyPage);

    await historyPage.searchTagPicker(TARGET_TAG);
    const row = historyPage.tagPickerItem(TARGET_TAG);
    await expect(row).toBeVisible();
    await row.click();
    await expect(historyPage.tagPickerApplyButton).toBeEnabled();

    await historyPage.searchTagPicker('');
    await expect(historyPage.tagPickerItem(fillerTagName(1))).toBeVisible();

    const reassign = waitForReassign(page);
    await historyPage.applyTagPicker();
    expect((await reassign).ok()).toBe(true);

    expect(await api.content.getImageTagIds(imageId)).toContain(targetTagId);
  });

  test('shows a late tag already on the image as checked', async ({
    browser,
  }) => {
    const { api, page, historyPage, imageId, targetTagId } = await openScene(
      browser,
      PAST_FIRST_PAGE_FILLERS,
    );
    await api.content.tagImage(imageId, targetTagId);
    expect(await api.content.getImageTagIds(imageId)).toContain(targetTagId);
    await selectOnlyImage(historyPage);

    await historyPage.searchTagPicker(TARGET_TAG);
    const row = historyPage.tagPickerItem(TARGET_TAG);
    await expect(row).toHaveAttribute('aria-pressed', 'true');

    await row.click();
    await expect(row).toHaveAttribute('aria-pressed', 'false');

    const reassign = waitForReassign(page);
    await historyPage.applyTagPicker();
    expect((await reassign).ok()).toBe(true);

    expect(await api.content.getImageTagIds(imageId)).not.toContain(
      targetTagId,
    );
  });

  test('sends one search for a fast-typed term and restores the default list without a request', async ({
    browser,
  }) => {
    const { page, historyPage } = await openScene(browser, FEW_FILLERS);
    const terms = captureTagListTerms(page);
    await selectOnlyImage(historyPage);

    await historyPage.searchTagPicker('');
    await historyPage.tagPickerSearchInput.pressSequentially(TARGET_TAG);
    await expect(historyPage.tagPickerItem(TARGET_TAG)).toBeVisible();
    await expect(historyPage.tagPickerItem(fillerTagName(1))).toHaveCount(0);
    expect(terms).toEqual(['', TARGET_TAG]);

    await historyPage.searchTagPicker('');
    await expect(historyPage.tagPickerItem(fillerTagName(1))).toBeVisible();
    await expect(historyPage.tagPickerItem(TARGET_TAG)).toBeVisible();

    const settled = settledRequest(page, isTagSearchFor(fillerTagName(2)));
    await historyPage.searchTagPicker(fillerTagName(2));
    await settled;
    await expect(historyPage.tagPickerItem(fillerTagName(2))).toBeVisible();

    expect(terms).toEqual(['', TARGET_TAG, fillerTagName(2)]);
  });

  test('keeps newer results when the same search is cleared and repeated before an older response arrives', async ({
    browser,
  }) => {
    const { api, page, historyPage } = await openScene(browser, FEW_FILLERS);
    await selectOnlyImage(historyPage);

    let staleCaptured = false;
    let releaseStale: () => void = () => {};
    const staleGate = new Promise<void>((resolve) => {
      releaseStale = resolve;
    });

    await page.route(isTagListUrl, async (route) => {
      if (searchTermOf(route.request()) !== TARGET_TAG || staleCaptured) {
        await route.continue();
        return;
      }

      const response = await route.fetch();
      staleCaptured = true;
      await staleGate;
      await route.fulfill({ response });
    });

    await historyPage.searchTagPicker(TARGET_TAG);
    await expect.poll(() => staleCaptured).toBe(true);

    const newTag = `${TARGET_TAG}-new`;
    await api.content.createTag(newTag);
    await historyPage.searchTagPicker('');
    await historyPage.searchTagPicker(TARGET_TAG);
    await expect(historyPage.tagPickerItem(newTag)).toBeVisible();

    const staleSettled = settledRequest(page, isTagSearchFor(TARGET_TAG));
    releaseStale();
    await staleSettled;
    await flushRendering(page);

    await expect(historyPage.tagPickerItem(newTag)).toBeVisible();
    await expect(historyPage.tagPickerItem(TARGET_TAG)).toBeVisible();
  });

  test('keeps the newer result when an older search answers late and shows no stale list meanwhile', async ({
    browser,
  }) => {
    const { page, historyPage } = await openScene(browser, FEW_FILLERS);
    await selectOnlyImage(historyPage);

    let staleRequested = false;
    let releaseStale: () => void = () => {};
    const staleGate = new Promise<void>((resolve) => {
      releaseStale = resolve;
    });

    await page.route(isTagListUrl, async (route) => {
      if (searchTermOf(route.request()) !== TARGET_TAG) {
        await route.continue();
        return;
      }

      staleRequested = true;
      await staleGate;
      await route.continue();
    });

    await historyPage.searchTagPicker(TARGET_TAG);
    await expect.poll(() => staleRequested).toBe(true);

    await expect(historyPage.tagPickerItem(fillerTagName(1))).toHaveCount(0);
    await expect(
      historyPage.tagPicker.getByText('No matches found'),
    ).toHaveCount(0);
    await expect(historyPage.tagPickerSearchInput).toBeVisible();

    await historyPage.searchTagPicker(fillerTagName(1));
    await expect(historyPage.tagPickerItem(fillerTagName(1))).toBeVisible();

    const staleSettled = settledRequest(page, isTagSearchFor(TARGET_TAG));
    releaseStale();
    await staleSettled;
    await flushRendering(page);

    await expect(historyPage.tagPickerItem(fillerTagName(1))).toBeVisible();
    await expect(historyPage.tagPickerItem(TARGET_TAG)).toHaveCount(0);
    await expect(historyPage.tagPickerSearchInput).toHaveValue(
      fillerTagName(1),
    );
  });
});
