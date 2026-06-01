import { expect, test } from '../fixtures/auth.fixture';

test.describe('Unavailable share', () => {
  test.use({ storageState: { cookies: [], origins: [] } });

  test('reroutes an expired image share to the unavailable page', async ({
    page,
    sharePage,
    contentApi,
  }) => {
    const seconds = 4;
    const imageId = await contentApi.uploadImage({ isPublic: false });
    const share = await contentApi.createImageShare(imageId);
    await contentApi.publishShare(share.shareId);

    const expiresAt = new Date(Date.now() + seconds * 1000).toISOString();
    await contentApi.setShareExpiration(share.shareId, expiresAt);

    await new Promise((resolve) => setTimeout(resolve, (seconds + 1) * 1000));

    await page.goto(`/image/${imageId}.png`);
    await sharePage.expectUnavailable();
  });

  test('shows the unavailable page for an unpublished image share', async ({
    page,
    sharePage,
    contentApi,
  }) => {
    const imageId = await contentApi.uploadImage({ isPublic: false });
    const share = await contentApi.createImageShare(imageId);
    await contentApi.publishShare(share.shareId);
    await contentApi.unpublishShare(share.shareId);

    await page.goto(`/image/${imageId}.png`);
    await sharePage.expectUnavailable();
  });
});
