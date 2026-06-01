import { expect, test } from '../fixtures/auth.fixture';

test.describe('Short code resolution', () => {
  test.use({ storageState: { cookies: [], origins: [] } });

  test('resolves a published image short code to the image content', async ({
    page,
    contentApi,
  }) => {
    const imageId = await contentApi.uploadImage({ isPublic: true });
    const share = await contentApi.publishImageShare(imageId);
    const code = contentApi.getShortCode(share);
    expect(code).not.toBeNull();

    const response = await page.goto(`/i/${code}`);
    await expect(page).toHaveURL(/\/image\/[0-9a-fA-F-]+\.[a-z0-9]+/);
    await expect(page).not.toHaveURL(/\/image\/unavailable/);
    expect(response?.headers()['content-type']).toContain('image');
  });

  test('resolves a published collection short code to the collection page', async ({
    page,
    contentApi,
  }) => {
    const collectionName = `Shared Collection ${Date.now()}`;
    const collectionId = await contentApi.createCollection({
      name: collectionName,
    });
    const imageId = await contentApi.uploadImage({ isPublic: true });
    await contentApi.addImageToCollection(collectionId, imageId);

    const share = await contentApi.publishCollectionShare(collectionId);
    const code = contentApi.getShortCode(share);
    expect(code).not.toBeNull();

    await page.goto(`/c/${code}`);
    await expect(page).toHaveURL(/\/collection\//);
    await expect(page).not.toHaveURL(/\/image\/unavailable/);
    await expect(
      page.getByRole('heading', { name: collectionName }),
    ).toBeVisible();
  });
});
