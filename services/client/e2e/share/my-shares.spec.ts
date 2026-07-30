import { expect, test } from '../fixtures/auth.fixture';

test.describe('My shares management', () => {
  test('unpublishes a share and removes its row', async ({
    api,
    sharesPage,
  }) => {
    const imageId = await api.content.uploadImage({ isPublic: true });
    await api.shares.publishImageShare(imageId);

    await sharesPage.goto();
    await expect(sharesPage.heading).toBeVisible();

    const row = sharesPage.rowForImage(imageId);
    await expect(row).toHaveCount(1);

    await sharesPage.unpublishRow(row);

    await expect(row).toHaveCount(0, { timeout: 15000 });
  });
});
