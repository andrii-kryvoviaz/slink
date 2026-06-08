import { expect, test } from '../fixtures/auth.fixture';
import { createUniquePng } from '../helpers/uniqueImage';

const INIT_URL = /\/api\/upload\/chunked$/;
const CHUNK_URL = /\/api\/upload\/chunked\/[^/]+\/\d+/;

async function mockInitSuccess(
  page: import('@playwright/test').Page,
): Promise<void> {
  await page.route(INIT_URL, async (route) => {
    if (route.request().method() !== 'POST') {
      await route.continue();
      return;
    }

    await route.fulfill({
      status: 201,
      contentType: 'application/json',
      body: JSON.stringify({
        data: {
          uploadId: 'e2e-upload',
          token: 'e2e-token',
          chunkSize: 50_000_000,
        },
      }),
    });
  });
}

test.describe('Auto-group multiple uploads', () => {
  test.afterEach(async ({ api }) => {
    await api.preferences.updatePreferences({
      'image.autoGroupBatchUploads': true,
    });
  });

  test('uploads multiple images as separate files when auto-group is disabled', async ({
    uploadPage,
    page,
    api,
  }) => {
    await api.preferences.updatePreferences({
      'image.autoGroupBatchUploads': false,
    });

    const before = new Set(
      (await api.content.listCollections()).map((c) => c.id),
    );

    await uploadPage.goto();
    await expect(uploadPage.heading).toBeVisible();
    await expect(uploadPage.autoGroupToggle).toHaveText(/Separate/);

    await uploadPage.uploadMultipleImages(2);

    await expect(page.getByRole('heading', { name: 'Complete' })).toBeVisible({
      timeout: 30000,
    });
    await expect(uploadPage.autoGroupBanner).toBeHidden();
    await expect(page).toHaveURL(/\/upload/);

    await expect
      .poll(
        async () =>
          (await api.content.listCollections())
            .map((c) => c.id)
            .filter((id) => !before.has(id)).length,
        { timeout: 10000 },
      )
      .toBe(0);

    await expect(uploadPage.goToUploadsButton).toBeVisible();

    await uploadPage.goToUploadsButton.click();
    await page.waitForURL(/\/history/, { timeout: 30000 });
  });

  test('does not leave a collection when every file in the batch fails', async ({
    uploadPage,
    page,
    api,
  }) => {
    await api.preferences.updatePreferences({
      'image.autoGroupBatchUploads': true,
    });

    await page.route(/\/api\/upload\/chunked$/, async (route) => {
      if (route.request().method() !== 'POST') {
        await route.continue();
        return;
      }
      await route.fulfill({
        status: 500,
        contentType: 'application/json',
        body: JSON.stringify({ error: { message: 'Internal Server Error' } }),
      });
    });

    const before = new Set(
      (await api.content.listCollections()).map((c) => c.id),
    );

    const first = createUniquePng();
    const second = createUniquePng();

    await uploadPage.goto();
    await expect(uploadPage.heading).toBeVisible();

    await uploadPage.uploadFilesExpectingRequest([
      { name: first.name, mimeType: 'image/png', buffer: first.buffer },
      { name: second.name, mimeType: 'image/png', buffer: second.buffer },
    ]);

    await expect(page.getByRole('heading', { name: 'Complete' })).toBeVisible({
      timeout: 30000,
    });
    await expect(page.getByText('2 failed', { exact: false })).toBeVisible();

    await expect(uploadPage.autoGroupBanner).toBeHidden();

    await expect
      .poll(
        async () =>
          (await api.content.listCollections())
            .map((c) => c.id)
            .filter((id) => !before.has(id)).length,
        { timeout: 10000 },
      )
      .toBe(0);

    await expect(uploadPage.goToUploadsButton).toBeVisible();
  });

  test('undo removes the auto-created collection and reveals the uploads link', async ({
    uploadPage,
    page,
    api,
  }) => {
    await api.preferences.updatePreferences({
      'image.autoGroupBatchUploads': true,
    });

    const before = new Set(
      (await api.content.listCollections()).map((c) => c.id),
    );

    await uploadPage.goto();
    await expect(uploadPage.heading).toBeVisible();

    await uploadPage.uploadMultipleImages(2);

    await expect(page.getByRole('heading', { name: 'Complete' })).toBeVisible({
      timeout: 30000,
    });
    await expect(uploadPage.autoGroupBanner).toBeVisible();

    await expect
      .poll(
        async () =>
          (await api.content.listCollections())
            .map((c) => c.id)
            .filter((id) => !before.has(id)).length,
        { timeout: 10000 },
      )
      .toBe(1);

    const newCollectionId = (await api.content.listCollections())
      .map((c) => c.id)
      .find((id) => !before.has(id))!;

    let imageIds: string[] = [];
    await expect
      .poll(
        async () => {
          try {
            imageIds = (
              await api.content.getCollectionItems(newCollectionId)
            ).map((item) => item.itemId);
            return imageIds.length;
          } catch {
            return -1;
          }
        },
        { timeout: 20000 },
      )
      .toBe(2);

    await uploadPage.undoButton.click();

    await expect(uploadPage.autoGroupBanner).toBeHidden();
    await expect(uploadPage.goToUploadsButton).toBeVisible();

    await expect
      .poll(
        async () => (await api.content.getCollection(newCollectionId)).status,
        {
          timeout: 10000,
        },
      )
      .toBe(404);

    for (const imageId of imageIds) {
      const detail = await api.content.getImageDetail(imageId);
      expect(detail.id).toBe(imageId);
    }
  });

  test('discards the auto-collection when a grouped batch is cancelled mid-upload', async ({
    uploadPage,
    page,
    api,
  }) => {
    await api.preferences.updatePreferences({
      'image.autoGroupBatchUploads': true,
    });

    const before = new Set(
      (await api.content.listCollections()).map((c) => c.id),
    );

    await mockInitSuccess(page);

    await page.route(CHUNK_URL, async (route) => {
      if (route.request().method() !== 'PUT') {
        await route.continue();
        return;
      }

      await new Promise(() => {});
    });

    const first = createUniquePng();
    const second = createUniquePng();

    await uploadPage.goto();
    await expect(uploadPage.heading).toBeVisible();

    await uploadPage.uploadFilesExpectingRequest([
      { name: first.name, mimeType: 'image/png', buffer: first.buffer },
      { name: second.name, mimeType: 'image/png', buffer: second.buffer },
    ]);

    await expect(uploadPage.cancelButton).toBeVisible({ timeout: 30000 });

    await uploadPage.cancelButton.click();

    await expect
      .poll(
        async () =>
          (await api.content.listCollections())
            .map((c) => c.id)
            .filter((id) => !before.has(id)).length,
        { timeout: 10000 },
      )
      .toBe(0);

    await expect(uploadPage.heading).toBeVisible();
  });

  test('keeps the auto-collection when one of two files succeeds', async ({
    uploadPage,
    page,
    api,
  }) => {
    await api.preferences.updatePreferences({
      'image.autoGroupBatchUploads': true,
    });

    const before = new Set(
      (await api.content.listCollections()).map((c) => c.id),
    );

    const failing = createUniquePng();
    const succeeding = createUniquePng();

    await page.route(/\/api\/upload\/chunked$/, async (route) => {
      const body = route.request().postDataJSON() as { fileName?: string };
      if (body?.fileName === failing.name) {
        await route.fulfill({
          status: 500,
          contentType: 'application/json',
          body: JSON.stringify({ error: { message: 'Internal Server Error' } }),
        });
        return;
      }
      await route.continue();
    });

    await uploadPage.goto();
    await expect(uploadPage.heading).toBeVisible();

    await uploadPage.uploadFilesExpectingRequest([
      { name: failing.name, mimeType: 'image/png', buffer: failing.buffer },
      {
        name: succeeding.name,
        mimeType: 'image/png',
        buffer: succeeding.buffer,
      },
    ]);

    await expect(page.getByRole('heading', { name: 'Complete' })).toBeVisible({
      timeout: 30000,
    });
    await expect(page.getByText('1 failed', { exact: false })).toBeVisible();

    await expect
      .poll(
        async () =>
          (await api.content.listCollections())
            .map((c) => c.id)
            .filter((id) => !before.has(id)).length,
        { timeout: 10000 },
      )
      .toBe(1);

    const newCollectionId = (await api.content.listCollections())
      .map((c) => c.id)
      .find((id) => !before.has(id))!;

    await expect
      .poll(
        async () => {
          try {
            return (await api.content.getCollectionItems(newCollectionId))
              .length;
          } catch {
            return -1;
          }
        },
        { timeout: 20000 },
      )
      .toBe(1);
  });

  test('shows the failed banner and uploads loose when the collection cannot be created', async ({
    uploadPage,
    page,
    api,
  }) => {
    await api.preferences.updatePreferences({
      'image.autoGroupBatchUploads': true,
    });

    const before = new Set(
      (await api.content.listCollections()).map((c) => c.id),
    );

    await page.route(/\/api\/collection$/, async (route) => {
      if (route.request().method() !== 'POST') {
        await route.continue();
        return;
      }

      await route.fulfill({
        status: 500,
        contentType: 'application/json',
        body: JSON.stringify({ error: { message: 'Internal Server Error' } }),
      });
    });

    await uploadPage.goto();
    await expect(uploadPage.heading).toBeVisible();

    await uploadPage.uploadMultipleImages(2);

    await expect(page.getByRole('heading', { name: 'Complete' })).toBeVisible({
      timeout: 30000,
    });
    await expect(uploadPage.autoGroupFailedBanner).toBeVisible();
    await expect(uploadPage.autoGroupBanner).toBeHidden();
    await expect(uploadPage.goToUploadsButton).toBeVisible();

    await expect
      .poll(
        async () =>
          (await api.content.listCollections())
            .map((c) => c.id)
            .filter((id) => !before.has(id)).length,
        { timeout: 10000 },
      )
      .toBe(0);
  });

  test('re-creates the collection when a fully-failed batch is retried', async ({
    uploadPage,
    page,
    api,
  }) => {
    await api.preferences.updatePreferences({
      'image.autoGroupBatchUploads': true,
    });

    const before = new Set(
      (await api.content.listCollections()).map((c) => c.id),
    );

    await page.route(/\/api\/upload\/chunked$/, async (route) => {
      if (route.request().method() !== 'POST') {
        await route.continue();
        return;
      }
      await route.fulfill({
        status: 500,
        contentType: 'application/json',
        body: JSON.stringify({ error: { message: 'Internal Server Error' } }),
      });
    });

    const first = createUniquePng();
    const second = createUniquePng();

    await uploadPage.goto();
    await expect(uploadPage.heading).toBeVisible();

    await uploadPage.uploadFilesExpectingRequest([
      { name: first.name, mimeType: 'image/png', buffer: first.buffer },
      { name: second.name, mimeType: 'image/png', buffer: second.buffer },
    ]);

    await expect(page.getByRole('heading', { name: 'Complete' })).toBeVisible({
      timeout: 30000,
    });
    await expect(page.getByText('2 failed', { exact: false })).toBeVisible();

    await expect
      .poll(
        async () =>
          (await api.content.listCollections())
            .map((c) => c.id)
            .filter((id) => !before.has(id)).length,
        { timeout: 10000 },
      )
      .toBe(0);

    await page.unroute(/\/api\/upload\/chunked$/);

    await uploadPage.retryFailedButton.click();

    await expect(uploadPage.retryFailedButton).toBeHidden({ timeout: 30000 });
    await expect(uploadPage.doneButton).toBeVisible({ timeout: 30000 });

    await expect
      .poll(
        async () =>
          (await api.content.listCollections())
            .map((c) => c.id)
            .filter((id) => !before.has(id)).length,
        { timeout: 20000 },
      )
      .toBe(1);

    const newCollectionId = (await api.content.listCollections())
      .map((c) => c.id)
      .find((id) => !before.has(id))!;

    await expect
      .poll(
        async () => {
          try {
            return (await api.content.getCollectionItems(newCollectionId))
              .length;
          } catch {
            return -1;
          }
        },
        { timeout: 20000 },
      )
      .toBe(2);
  });
});
