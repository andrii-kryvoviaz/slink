import { expect, test } from '../fixtures/auth.fixture';

test.describe('Upload errors', () => {
  test('rejects a non-image file and does not start an upload', async ({
    uploadPage,
    page,
  }) => {
    let uploadRequested = false;
    page.on('request', (request) => {
      if (
        request.url().includes('/api/upload') &&
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
  }) => {
    const uploadStatuses: number[] = [];
    page.on('response', (response) => {
      if (
        response.url().includes('/api/upload') &&
        response.request().method() === 'POST'
      ) {
        uploadStatuses.push(response.status());
      }
    });

    await uploadPage.goto();
    await expect(uploadPage.heading).toBeVisible();

    await uploadPage.uploadMultipleImages(2);

    await page.waitForURL(/\/collection\//, { timeout: 30000 });

    expect(uploadStatuses.length).toBeGreaterThanOrEqual(2);
    expect(
      uploadStatuses.every((status) => status >= 200 && status < 300),
    ).toBe(true);
    await expect(page.getByText(/items/).first()).toBeVisible();
  });
});
