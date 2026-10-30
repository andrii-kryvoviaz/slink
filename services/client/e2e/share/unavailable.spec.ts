import { expect, test } from '../fixtures/auth.fixture';

const SHORT_EXPIRY_MS = 3 * 1000;

test.describe('Unavailable share', { tag: '@anonymous' }, () => {
  test('reroutes an expired image share to the unavailable page', async ({
    page,
    sharePage,
    api,
  }) => {
    const imageId = await api.content.uploadImage({ isPublic: false });
    const share = await api.shares.createImageShare(imageId);
    await api.shares.publishShare(share.shareId);

    const expiresAt = new Date(Date.now() + SHORT_EXPIRY_MS).toISOString();
    await api.shares.setShareExpiration(share.shareId, expiresAt);

    await expect(async () => {
      await page.goto(`/image/${imageId}.png`);
      await expect(sharePage.unavailableHeading).toBeVisible({ timeout: 1000 });
    }).toPass({ timeout: 15000 });
  });

  test('shows the unavailable page for an unpublished image share', async ({
    page,
    sharePage,
    api,
  }) => {
    const imageId = await api.content.uploadImage({ isPublic: false });
    const share = await api.shares.createImageShare(imageId);
    await api.shares.publishShare(share.shareId);
    await api.shares.unpublishShare(share.shareId);

    await page.goto(`/image/${imageId}.png`);
    await sharePage.expectUnavailable();
  });
});
