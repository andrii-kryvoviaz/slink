import { expect, test } from '../fixtures/auth.fixture';

test.describe('My shares management', () => {
  test('unpublishes a share and removes its row', async ({
    api,
    page,
    sharesPage,
  }) => {
    const imageId = await api.content.uploadImage({ isPublic: true });
    await api.shares.publishImageShare(imageId);

    await sharesPage.goto();
    await expect(sharesPage.heading).toBeVisible();

    await expect(sharesPage.actionsTrigger.first()).toBeVisible();

    const rowCountBefore = await sharesPage.actionsTrigger.count();

    await sharesPage.unpublishFirst();

    await expect(async () => {
      const remaining = await sharesPage.actionsTrigger.count();
      const emptyVisible = await sharesPage.emptyHeading.isVisible();
      expect(remaining < rowCountBefore || emptyVisible).toBe(true);
    }).toPass({ timeout: 15000 });
  });
});
