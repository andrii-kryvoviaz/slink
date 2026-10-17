import type { Browser, BrowserContext, Page } from '@playwright/test';

import { expect, test } from '../fixtures/auth.fixture';
import { unique } from '../helpers/accounts';
import type { ApiClient } from '../helpers/api';
import { provisionUser } from '../helpers/provisioning';
import { signInContext } from '../helpers/session';
import { seedTags } from '../helpers/tags';
import { HistoryPage } from '../pages/HistoryPage';
import { ImageInfoPage } from '../pages/ImageInfoPage';

const TARGET_TAG = 'tv';
const FEW_FILLERS = 3;
const PAST_FIRST_PAGE_FILLERS = 55;

type Scene = {
  api: ApiClient;
  page: Page;
  imageId: string;
  targetTagId: string;
};

test.describe('Single-image tag picker search', { tag: '@sc-998' }, () => {
  let context: BrowserContext | undefined;

  const openScene = async (
    browser: Browser,
    fillerCount: number,
  ): Promise<Scene> => {
    const account = unique('imgtagsrch');
    const api = await provisionUser(account);
    const imageId = await api.content.uploadImage();
    const targetTagId = await seedTags(api, fillerCount, TARGET_TAG);

    context = await signInContext(browser, account);
    const page = await context.newPage();

    return { api, page, imageId, targetTagId };
  };

  test.afterEach(async () => {
    await context?.close();
    context = undefined;
  });

  test('finds and assigns a tag that sorts after the first fifty tags on the info page', async ({
    browser,
  }) => {
    const { api, page, imageId, targetTagId } = await openScene(
      browser,
      PAST_FIRST_PAGE_FILLERS,
    );
    const imageInfoPage = new ImageInfoPage(page);
    await imageInfoPage.goto(imageId);

    await imageInfoPage.searchTags(TARGET_TAG);

    const row = imageInfoPage.tagPickerItem(TARGET_TAG);
    await expect(row).toBeVisible();
    await expect(row).toHaveAttribute('aria-pressed', 'false');
    await expect(
      imageInfoPage.tagPicker.getByText('No matches found'),
    ).toHaveCount(0);

    await row.click();

    await expect
      .poll(() => api.content.getImageTagIds(imageId), { timeout: 15000 })
      .toContain(targetTagId);
  });

  test('shows the search input on the info page when the user has only a few tags', async ({
    browser,
  }) => {
    const { page, imageId } = await openScene(browser, FEW_FILLERS);
    const imageInfoPage = new ImageInfoPage(page);
    await imageInfoPage.goto(imageId);

    await imageInfoPage.openTagPicker();

    await expect(imageInfoPage.tagPickerItem(TARGET_TAG)).toBeVisible();
    await expect(imageInfoPage.tagSearchInput).toBeVisible();
  });

  test('lists a searched tag in the history row after assigning it from the row menu', async ({
    browser,
  }) => {
    const { page, imageId } = await openScene(browser, PAST_FIRST_PAGE_FILLERS);
    const historyPage = new HistoryPage(page);
    await historyPage.useTableView();
    await historyPage.goto();

    await historyPage.searchRowTags(imageId, TARGET_TAG);

    const row = historyPage.rowTagPickerItem(TARGET_TAG);
    await expect(row).toBeVisible();
    await expect(row).toHaveAttribute('aria-pressed', 'false');

    await row.click();

    await expect(
      historyPage.tableRowTagLink(imageId, TARGET_TAG),
    ).toBeVisible();
  });

  test.describe(
    'Tag lists retain tags outside the picker catalog',
    { tag: '@sc-1003' },
    () => {
      test('keeps a never-fetched tag in the history row after adding a default-list tag', async ({
        browser,
      }) => {
        const { api, page, imageId, targetTagId } = await openScene(
          browser,
          PAST_FIRST_PAGE_FILLERS,
        );
        await api.content.tagImage(imageId, targetTagId);
        const historyPage = new HistoryPage(page);
        await historyPage.useTableView();
        await historyPage.goto();
        await expect(
          historyPage.tableRowTagLink(imageId, TARGET_TAG),
        ).toBeVisible();

        await historyPage.searchRowTags(imageId, 'a01');
        await historyPage.rowTagPickerItem('a01').click();

        await expect
          .poll(() => api.content.getImageTagIds(imageId), { timeout: 15000 })
          .toHaveLength(2);
        await expect(historyPage.tableRowTagLink(imageId, 'a01')).toBeVisible();
        await expect(
          historyPage.tableRowTagLink(imageId, TARGET_TAG),
        ).toBeVisible();
      });

      test('keeps a never-fetched tag on the info page after adding a default-list tag and reloading', async ({
        browser,
      }) => {
        const { api, page, imageId, targetTagId } = await openScene(
          browser,
          PAST_FIRST_PAGE_FILLERS,
        );
        await api.content.tagImage(imageId, targetTagId);
        const imageInfoPage = new ImageInfoPage(page);
        await imageInfoPage.goto(imageId);
        await expect(imageInfoPage.tagLink(TARGET_TAG)).toBeVisible();

        await imageInfoPage.searchTags('a01');
        await imageInfoPage.tagPickerItem('a01').click();

        await expect
          .poll(() => api.content.getImageTagIds(imageId), { timeout: 15000 })
          .toHaveLength(2);
        await expect(imageInfoPage.tagLink('a01')).toBeVisible();
        await expect(imageInfoPage.tagLink(TARGET_TAG)).toBeVisible();

        await page.reload();
        await expect(imageInfoPage.tagLink('a01')).toBeVisible();
        await expect(imageInfoPage.tagLink(TARGET_TAG)).toBeVisible();
      });

      test('keeps a never-fetched tag in the history row after removing a default-list tag', async ({
        browser,
      }) => {
        const { api, page, imageId, targetTagId } = await openScene(
          browser,
          PAST_FIRST_PAGE_FILLERS,
        );
        const removedTagId = await api.content.createTag('a00');
        await api.content.tagImage(imageId, removedTagId);
        await api.content.tagImage(imageId, targetTagId);
        const historyPage = new HistoryPage(page);
        await historyPage.useTableView();
        await historyPage.goto();
        await expect(historyPage.tableRowTagLink(imageId, 'a00')).toBeVisible();
        await expect(
          historyPage.tableRowTagLink(imageId, TARGET_TAG),
        ).toBeVisible();

        await historyPage.searchRowTags(imageId, 'a00');
        await historyPage.rowTagPickerItem('a00').click();

        await expect
          .poll(() => api.content.getImageTagIds(imageId), { timeout: 15000 })
          .toEqual([targetTagId]);
        await expect(
          historyPage.tableRowTagLink(imageId, TARGET_TAG),
        ).toBeVisible();
        await expect(historyPage.tableRowTagLink(imageId, 'a00')).toHaveCount(
          0,
        );
      });
    },
  );
});
