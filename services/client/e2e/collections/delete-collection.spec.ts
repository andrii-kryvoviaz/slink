import { expect, test } from '../fixtures/auth.fixture';
import type { ApiClient } from '../helpers/api';

function uniqueCollectionName(): string {
  return `e2e-delete-${Date.now()}-${Math.floor(Math.random() * 1e6)}`;
}

async function seedCollection(api: ApiClient, imageCount: number) {
  const collectionId = await api.content.createCollection({
    name: uniqueCollectionName(),
  });

  const imageIds: string[] = [];
  for (let index = 0; index < imageCount; index++) {
    const imageId = await api.content.uploadImage();
    await api.content.addImageToCollection(collectionId, imageId);
    imageIds.push(imageId);
  }

  return { collectionId, imageIds };
}

async function collectionStatus(
  api: ApiClient,
  collectionId: string,
): Promise<number> {
  const { status } = await api.content.getCollection(collectionId);
  return status;
}

async function imageStatus(api: ApiClient, imageId: string): Promise<number> {
  const { status } = await api.content.getImage(imageId);
  return status;
}

test.describe('Delete collection', () => {
  test('deletes the collection from the list and keeps its images', async ({
    api,
    collectionsPage,
    page,
  }) => {
    const { collectionId, imageIds } = await seedCollection(api, 2);

    await collectionsPage.goto();
    await expect(collectionsPage.heading).toBeVisible();
    await collectionsPage.setViewMode('table');

    const row = collectionsPage.rowFor(collectionId);
    await expect(row).toHaveCount(1);

    await collectionsPage.openDeleteConfirmation(collectionId);

    await expect(collectionsPage.deleteImagesSwitch).toHaveAttribute(
      'aria-checked',
      'false',
    );
    await expect(
      page.getByText(`Delete all ${imageIds.length} images in this collection`),
    ).toBeVisible();

    await collectionsPage.confirmDelete();

    await expect(row).toHaveCount(0);

    await expect.poll(() => collectionStatus(api, collectionId)).toBe(404);

    for (const imageId of imageIds) {
      expect(await imageStatus(api, imageId)).toBe(200);
    }
  });

  test('deletes the collection and its images when delete images is enabled', async ({
    api,
  }) => {
    const { collectionId, imageIds } = await seedCollection(api, 2);

    await api.content.deleteCollection(collectionId, { deleteImages: true });

    await expect.poll(() => collectionStatus(api, collectionId)).toBe(404);

    for (const imageId of imageIds) {
      await expect.poll(() => imageStatus(api, imageId)).toBe(404);
    }
  });
});
