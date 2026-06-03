import { expect, test } from '../fixtures/auth.fixture';

test.describe('Short code resolution', () => {
  test.use({ storageState: { cookies: [], origins: [] } });

  test('resolves a published image short code to the image content', async ({
    page,
    api,
  }) => {
    const imageId = await api.content.uploadImage({ isPublic: true });
    const share = await api.shares.publishImageShare(imageId);
    const code = api.shares.getShortCode(share);
    expect(code).not.toBeNull();

    const response = await page.goto(`/i/${code}`);
    await expect(page).toHaveURL(/\/image\/[0-9a-fA-F-]+\.[a-z0-9]+/);
    await expect(page).not.toHaveURL(/\/image\/unavailable/);
    expect(response?.headers()['content-type']).toContain('image');
  });

  test('resolves a published collection short code to the collection page', async ({
    page,
    api,
  }) => {
    const collectionName = `Shared Collection ${Date.now()}`;
    const collectionId = await api.content.createCollection({
      name: collectionName,
    });
    const imageId = await api.content.uploadImage({ isPublic: true });
    await api.content.addImageToCollection(collectionId, imageId);

    const share = await api.shares.publishCollectionShare(collectionId);
    const code = api.shares.getShortCode(share);
    expect(code).not.toBeNull();

    await page.goto(`/c/${code}`);
    await expect(page).toHaveURL(/\/collection\//);
    await expect(page).not.toHaveURL(/\/image\/unavailable/);
    await expect(
      page.getByRole('heading', { name: collectionName }),
    ).toBeVisible();
  });
});
