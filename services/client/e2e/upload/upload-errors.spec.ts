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

    const after = new Set(
      (await api.content.listCollections()).map((c) => c.id),
    );
    expect([...after].filter((id) => !before.has(id))).toHaveLength(0);
  });
});
