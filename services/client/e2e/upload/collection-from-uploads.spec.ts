import { expect, test } from '../fixtures/auth.fixture';
import type { ApiClient } from '../helpers/api';

const POLL_TIMEOUT = 20000;

function uniqueCollectionName(): string {
  return `e2e-collection-${Date.now()}-${Math.floor(Math.random() * 1e6)}`;
}

async function collectionIds(api: ApiClient): Promise<Set<string>> {
  return new Set((await api.content.listCollections()).map((c) => c.id));
}

async function diffNewCollectionIds(
  api: ApiClient,
  before: Set<string>,
): Promise<string[]> {
  const after = await collectionIds(api);
  return [...after].filter((id) => !before.has(id));
}

async function countCollectionItems(
  api: ApiClient,
  id: string,
): Promise<number> {
  try {
    return (await api.content.getCollectionItems(id)).length;
  } catch {
    return -1;
  }
}

test.describe('Collection from uploads', () => {
  test('batch upload does not auto-create a collection; banner offers grouping', async ({
    uploadPage,
    page,
    api,
  }) => {
    const before = await collectionIds(api);

    await uploadPage.goto();
    await expect(uploadPage.heading).toBeVisible();

    await uploadPage.uploadMultipleImages(2);

    await expect(page.getByRole('heading', { name: 'Complete' })).toBeVisible({
      timeout: 30000,
    });

    await expect(uploadPage.collectionBanner).toBeVisible();
    await expect(uploadPage.createCollectionButton).toBeVisible();
    await expect(uploadPage.collectionNameInput).toBeVisible();

    expect(await diffNewCollectionIds(api, before)).toHaveLength(0);
  });

  test('Create collection groups the uploaded images privately', async ({
    uploadPage,
    page,
    api,
  }) => {
    const before = await collectionIds(api);
    const name = uniqueCollectionName();

    await uploadPage.goto();
    await expect(uploadPage.heading).toBeVisible();

    await uploadPage.uploadMultipleImages(2);

    await expect(page.getByRole('heading', { name: 'Complete' })).toBeVisible({
      timeout: 30000,
    });

    await uploadPage.collectionNameInput.fill(name);
    await uploadPage.createCollectionButton.click();

    let createdId = '';
    await expect(async () => {
      const created = await diffNewCollectionIds(api, before);
      expect(created).toHaveLength(1);
      createdId = created[0];
      expect(await countCollectionItems(api, createdId)).toBe(2);
    }).toPass({ timeout: POLL_TIMEOUT });

    await expect(uploadPage.collectionCreatedHeading).toBeVisible();
    await expect(page.getByText(name)).toBeVisible();

    expect(await api.content.getCollectionSharing(createdId)).toBeNull();
  });

  test('Publish collection groups and shares the images', async ({
    uploadPage,
    page,
    api,
  }) => {
    const before = await collectionIds(api);
    const name = uniqueCollectionName();

    await uploadPage.goto();
    await expect(uploadPage.heading).toBeVisible();

    await uploadPage.uploadMultipleImages(2);

    await expect(page.getByRole('heading', { name: 'Complete' })).toBeVisible({
      timeout: 30000,
    });

    await uploadPage.collectionNameInput.fill(name);
    await uploadPage.createCollectionButton.click();

    let createdId = '';
    await expect(async () => {
      const created = await diffNewCollectionIds(api, before);
      expect(created).toHaveLength(1);
      createdId = created[0];
      expect(await countCollectionItems(api, createdId)).toBe(2);
    }).toPass({ timeout: POLL_TIMEOUT });

    await expect(uploadPage.collectionCreatedHeading).toBeVisible();

    await uploadPage.createPublicationButton.click();

    await expect(async () => {
      expect(await api.content.getCollectionSharing(createdId)).not.toBeNull();
    }).toPass({ timeout: POLL_TIMEOUT });
  });

  test('View collection navigates to the collection page', async ({
    uploadPage,
    page,
    api,
  }) => {
    const before = await collectionIds(api);
    const name = uniqueCollectionName();

    await uploadPage.goto();
    await expect(uploadPage.heading).toBeVisible();

    await uploadPage.uploadMultipleImages(2);

    await expect(page.getByRole('heading', { name: 'Complete' })).toBeVisible({
      timeout: 30000,
    });

    await uploadPage.collectionNameInput.fill(name);
    await uploadPage.createCollectionButton.click();

    await expect(async () => {
      expect(await diffNewCollectionIds(api, before)).toHaveLength(1);
    }).toPass({ timeout: POLL_TIMEOUT });

    await expect(uploadPage.viewCollectionButton).toBeVisible();
    await uploadPage.viewCollectionButton.click();

    await page.waitForURL(/\/collection\//, { timeout: 30000 });
  });

  test('Done returns to the upload form', async ({ uploadPage, page }) => {
    await uploadPage.goto();
    await expect(uploadPage.heading).toBeVisible();

    await uploadPage.uploadMultipleImages(2);

    await expect(page.getByRole('heading', { name: 'Complete' })).toBeVisible({
      timeout: 30000,
    });

    await expect(uploadPage.createCollectionButton).toBeVisible();

    await uploadPage.uploadMoreButton.click();

    await expect(uploadPage.heading).toBeVisible();
    await expect(uploadPage.fileInput).toBeAttached();
    await expect(uploadPage.createCollectionButton).toBeHidden();
  });
});
