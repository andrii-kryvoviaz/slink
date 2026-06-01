import { expect, test } from '../fixtures/auth.fixture';

const PAGE_SIZE = 2;
const TOTAL_IMAGES = 5;
const BASE_URL = process.env.E2E_BASE_URL ?? 'http://localhost:3100';

test.use({ storageState: 'e2e/.auth/user.json' });

test.describe('Collection paginated loading', () => {
  test.beforeEach(async ({ page }) => {
    await page.context().addCookies([
      {
        name: 'settings.collections',
        value: JSON.stringify({
          viewMode: 'grid',
          pageSize: PAGE_SIZE,
          loadStrategy: 'load_more',
        }),
        url: BASE_URL,
      },
    ]);
  });

  test('appends more items via the Load More button', async ({
    contentApi,
    page,
  }) => {
    const collectionId = await contentApi.createCollection({
      name: `Paginated ${Date.now()}`,
    });

    for (let i = 0; i < TOTAL_IMAGES; i++) {
      const imageId = await contentApi.uploadImage({ isPublic: false });
      await contentApi.addImageToCollection(collectionId, imageId);
    }

    await page.goto(`/collection/${collectionId}`);

    const cards = page.locator('main [role="button"][tabindex="0"]');
    await expect(cards.first()).toBeVisible();
    await expect(cards).toHaveCount(PAGE_SIZE);

    const loadMore = page.getByRole('button', {
      name: 'Load More',
      exact: true,
    });
    await expect(loadMore).toBeVisible();

    await loadMore.click();
    await expect(cards).toHaveCount(PAGE_SIZE * 2);

    await loadMore.click();
    await expect(cards).toHaveCount(TOTAL_IMAGES);
    await expect(loadMore).toBeHidden();
  });
});
