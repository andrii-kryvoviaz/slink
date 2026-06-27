import type { BrowserContext, Page } from '@playwright/test';

import { expect, test } from '../fixtures/auth.fixture';
import { unique } from '../helpers/accounts';
import type { ApiClient } from '../helpers/api';
import { provisionUser } from '../helpers/provisioning';
import { signInContext } from '../helpers/session';
import { HistoryPage } from '../pages/HistoryPage';

const TOTAL_UPLOADS = 5;

const waitForVisibility = (page: Page) =>
  page.waitForResponse(
    (response) =>
      response.url().includes('/api/images/batch') &&
      response.request().method() === 'PATCH',
  );

test.describe('History batch visibility @serial', () => {
  test.describe.configure({ mode: 'serial' });

  let context: BrowserContext;
  let page: Page;
  let historyPage: HistoryPage;
  let api: ApiClient;

  test.beforeAll(async ({ browser }) => {
    const account = unique('vis');
    api = await provisionUser(account);

    for (let index = 1; index <= TOTAL_UPLOADS; index++) {
      const name = `bvisibility-${String(index).padStart(2, '0')}.png`;
      await api.content.uploadImage({ fileName: name, isPublic: false });
    }

    context = await signInContext(browser, account);
    page = await context.newPage();
    historyPage = new HistoryPage(page);
    await historyPage.useGridView();
  });

  test.afterAll(async () => {
    await context?.close();
  });

  test('making images public updates them in place without a page jump', async () => {
    await historyPage.goto();
    await historyPage.gridCards.first().waitFor({ state: 'visible' });
    await expect(historyPage.gridCards).toHaveCount(TOTAL_UPLOADS);

    const before = await historyPage.visibleImageIds();
    const selected = before.slice(0, 3);

    await historyPage.selectImages(3);
    await expect(historyPage.actionBar).toBeVisible();

    const visibility = waitForVisibility(page);
    await historyPage.setVisibility('public');
    const response = await visibility;
    expect(response.ok()).toBe(true);

    await expect(historyPage.gridCards).toHaveCount(TOTAL_UPLOADS);
    await expect
      .poll(async () => (await historyPage.visibleImageIds()).toSorted(), {
        timeout: 15000,
      })
      .toEqual(before.toSorted());

    for (const id of selected) {
      await expect
        .poll(() => api.content.getImageVisibility(id), { timeout: 15000 })
        .toBe(true);
    }
  });
});
