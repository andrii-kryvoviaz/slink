import type { Browser, BrowserContext } from '@playwright/test';

import { expect, test } from '../fixtures/auth.fixture';
import { type Account, unique } from '../helpers/accounts';
import type { ApiClient } from '../helpers/api';
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
    let account: Account;
    let api: ApiClient;
    let targetId: string;
    let olderId: string;

    test.beforeAll(async () => {
      account = unique('imgcolkeep');
      api = await provisionUser(account);
      ({ targetId, olderId } = await seedCollectionsAhead(
        api,
        TARGET_COLLECTION,
      ));
    });

    const openAssignedScene = async (
      browser: Browser,
      includeTarget = false,
    ) => {
      const imageId = await api.content.uploadImage();
      await api.content.addImageToCollection(olderId, imageId);

      if (includeTarget) {
        await api.content.addImageToCollection(targetId, imageId);
      }

      context = await signInContext(browser, account);
      const imageInfoPage = new ImageInfoPage(await context.newPage());
      await imageInfoPage.goto(imageId);

      return { imageId, imageInfoPage };
    };

    test.afterEach(async () => {
      await context?.close();
      context = undefined;
    });

    test(
      'keeps a never-fetched collection listed when adding a searched collection and after reload',
      { tag: '@sc-1002' },
      async ({ browser }) => {
        const { imageId, imageInfoPage } = await openAssignedScene(browser);
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
        const { imageId, imageInfoPage } = await openAssignedScene(
          browser,
          true,
        );
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
