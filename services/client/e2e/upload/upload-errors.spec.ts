import { expect, test } from '../fixtures/auth.fixture';
import { isChunkedUploadCompletionResponse } from '../helpers/chunkedUpload';

test.describe('Upload errors', () => {
  test('rejects a non-image file and does not start an upload', async ({
    uploadPage,
    page,
  }) => {
    let uploadRequested = false;
    page.on('request', (request) => {
      if (
        /\/api\/upload\/chunked$/.test(request.url()) &&
        request.method() === 'POST'
      ) {
        uploadRequested = true;
      }
    });

    await uploadPage.goto();
    await expect(uploadPage.heading).toBeVisible();

    await uploadPage.uploadNonImageFile();

    await expect(uploadPage.successHeading).toBeHidden();
    await expect(uploadPage.multiUploadHeading).toBeHidden();
    await expect(uploadPage.heading).toBeVisible();
    await expect(page).toHaveURL(/\/upload/);
    expect(uploadRequested).toBe(false);
  });

  test('uploads two valid files and both succeed', async ({
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

    const uploadStatuses: number[] = [];
    page.on('response', (response) => {
      if (isChunkedUploadCompletionResponse(response)) {
        uploadStatuses.push(response.status());
      }
    });

    await uploadPage.goto();
    await expect(uploadPage.heading).toBeVisible();

    await uploadPage.uploadMultipleImages(2);

    await expect(page.getByRole('heading', { name: 'Complete' })).toBeVisible({
      timeout: 30000,
    });

    expect(uploadStatuses.length).toBeGreaterThanOrEqual(2);
    expect(
      uploadStatuses.every((status) => status >= 200 && status < 300),
    ).toBe(true);

    await expect(page).toHaveURL(/\/upload/);
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

    await uploadPage.viewCollectionButton.click();

    await page.waitForURL(/\/collection\//, { timeout: 30000 });
    await expect(page.getByText(/items/).first()).toBeVisible();
  });
});
