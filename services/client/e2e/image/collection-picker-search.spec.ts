import type { Browser, BrowserContext } from '@playwright/test';

import { expect, test } from '../fixtures/auth.fixture';
import { unique } from '../helpers/accounts';
import {
  FILLER_PATTERN,
  OLDER_COLLECTION,
  seedCollectionsAhead,
} from '../helpers/collections';
import { provisionUser } from '../helpers/provisioning';
import { signInContext } from '../helpers/session';
import { ImageInfoPage } from '../pages/ImageInfoPage';

const TARGET_COLLECTION = 'Harbor Archive';

test.describe(
  'Single-image collection picker search',
  { tag: '@sc-1000' },
  () => {
    let context: BrowserContext | undefined;

    const openAssignedScene = async (
      browser: Browser,
      includeTarget = false,
    ) => {
      const account = unique('imgcolkeep');
      const api = await provisionUser(account);
      const imageId = await api.content.uploadImage();
      const { targetId, olderId } = await seedCollectionsAhead(
        api,
        TARGET_COLLECTION,
      );
      await api.content.addImageToCollection(olderId, imageId);

      if (includeTarget) {
        await api.content.addImageToCollection(targetId, imageId);
      }

      context = await signInContext(browser, account);
      const imageInfoPage = new ImageInfoPage(await context.newPage());
      await imageInfoPage.goto(imageId);

      return { api, imageId, targetId, olderId, imageInfoPage };
    };

    test.afterEach(async () => {
      await context?.close();
      context = undefined;
    });

    test('finds and adds a collection past the first fifty on the info page', async ({
      browser,
    }) => {
      const account = unique('imgcolsrch');
      const api = await provisionUser(account);
      const imageId = await api.content.uploadImage();
      const { targetId } = await seedCollectionsAhead(api, TARGET_COLLECTION);

      context = await signInContext(browser, account);
      const imageInfoPage = new ImageInfoPage(await context.newPage());
      await imageInfoPage.goto(imageId);

      await imageInfoPage.openCollectionPicker();
      await expect(
        imageInfoPage.collectionPicker
          .getByRole('button', { name: FILLER_PATTERN })
          .first(),
      ).toBeVisible();
      await expect(
        imageInfoPage.collectionPickerItem(TARGET_COLLECTION),
      ).toHaveCount(0);

      await imageInfoPage.searchCollections(TARGET_COLLECTION);

      const row = imageInfoPage.collectionPickerItem(TARGET_COLLECTION);
      await expect(row).toBeVisible();
      await expect(row).toHaveAttribute('aria-pressed', 'false');
      await expect(
        imageInfoPage.collectionPicker.getByText('No matches found'),
      ).toHaveCount(0);

      await row.click();

      await expect
        .poll(
          async () =>
            (await api.content.getCollectionItems(targetId)).map(
              (item) => item.itemId,
            ),
          { timeout: 15000 },
        )
        .toContain(imageId);
    });

    test(
      'keeps a never-fetched collection listed when adding a searched collection and after reload',
      { tag: '@sc-1002' },
      async ({ browser }) => {
        const { api, imageId, targetId, olderId, imageInfoPage } =
          await openAssignedScene(browser);
        await expect(imageInfoPage.collectionLink(olderId)).toBeVisible();
        await expect(imageInfoPage.collectionLinks).toHaveCount(1);

        await imageInfoPage.openCollectionPicker();
        await expect(
          imageInfoPage.collectionPicker
            .getByRole('button', { name: FILLER_PATTERN })
            .first(),
        ).toBeVisible();
        await expect(
          imageInfoPage.collectionPickerItem(OLDER_COLLECTION),
        ).toHaveCount(0);
        await imageInfoPage.searchCollections(TARGET_COLLECTION);
        const row = imageInfoPage.collectionPickerItem(TARGET_COLLECTION);
        await expect(row).toHaveAttribute('aria-pressed', 'false');
        await row.click();

        await expect
          .poll(
            async () =>
              (await api.content.getCollectionItems(targetId)).map(
                (item) => item.itemId,
              ),
            { timeout: 15000 },
          )
          .toContain(imageId);
        await expect(imageInfoPage.collectionLink(targetId)).toBeVisible();
        await expect(imageInfoPage.collectionLink(olderId)).toBeVisible();
        await expect(imageInfoPage.collectionLinks).toHaveCount(2);

        await imageInfoPage.page.reload();
        await expect(imageInfoPage.collectionLink(targetId)).toBeVisible();
        await expect(imageInfoPage.collectionLink(olderId)).toBeVisible();
        await expect(imageInfoPage.collectionLinks).toHaveCount(2);
      },
    );

    test(
      'removes only the searched collection while keeping a never-fetched collection and after reload',
      { tag: '@sc-1002' },
      async ({ browser }) => {
        const { api, imageId, targetId, olderId, imageInfoPage } =
          await openAssignedScene(browser, true);
        await expect(imageInfoPage.collectionLink(olderId)).toBeVisible();
        await expect(imageInfoPage.collectionLink(targetId)).toBeVisible();
        await expect(imageInfoPage.collectionLinks).toHaveCount(2);

        await imageInfoPage.openCollectionPicker();
        await expect(
          imageInfoPage.collectionPicker
            .getByRole('button', { name: FILLER_PATTERN })
            .first(),
        ).toBeVisible();
        await expect(
          imageInfoPage.collectionPickerItem(OLDER_COLLECTION),
        ).toHaveCount(0);
        await imageInfoPage.searchCollections(TARGET_COLLECTION);
        const row = imageInfoPage.collectionPickerItem(TARGET_COLLECTION);
        await expect(row).toHaveAttribute('aria-pressed', 'true');
        await row.click();

        await expect
          .poll(
            async () =>
              (await api.content.getCollectionItems(targetId)).map(
                (item) => item.itemId,
              ),
            { timeout: 15000 },
          )
          .not.toContain(imageId);
        await expect(imageInfoPage.collectionLink(targetId)).toHaveCount(0);
        await expect(imageInfoPage.collectionLink(olderId)).toBeVisible();
        await expect(imageInfoPage.collectionLinks).toHaveCount(1);

        await imageInfoPage.page.reload();
        await expect(imageInfoPage.collectionLink(targetId)).toHaveCount(0);
        await expect(imageInfoPage.collectionLink(olderId)).toBeVisible();
        await expect(imageInfoPage.collectionLinks).toHaveCount(1);
      },
    );
  },
);
