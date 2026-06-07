import { expect, test } from '../fixtures/auth.fixture';
import { createUniquePng } from '../helpers/uniqueImage';

test.describe('Upload failure feedback', () => {
  test('shows an error toast when the backend rejects a single upload', async ({
    uploadPage,
    page,
  }) => {
    await page.route(/\/api\/upload\/chunked$/, async (route) => {
      await route.fulfill({
        status: 413,
        contentType: 'application/json',
        body: JSON.stringify({
          error: { message: 'File too large. Please choose a smaller file.' },
        }),
      });
    });

    await uploadPage.goto();
    await expect(uploadPage.heading).toBeVisible();

    await uploadPage.uploadSingleImage();

    const toast = await uploadPage.waitForToast();
    await expect(toast).toContainText(/too large/i);

    await expect(uploadPage.successHeading).toBeHidden();
    await expect(uploadPage.heading).toBeVisible();
    await expect(uploadPage.fileInput).toBeAttached();
  });

  test('shows a per-row error and a retry control when one of two uploads fails', async ({
    uploadPage,
    page,
  }) => {
    const failing = createUniquePng();
    const succeeding = createUniquePng();

    await page.route(/\/api\/upload\/chunked$/, async (route) => {
      const body = route.request().postDataJSON() as { fileName?: string };
      if (body?.fileName === failing.name) {
        await route.fulfill({
          status: 500,
          contentType: 'text/plain',
          body: 'Internal Server Error',
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

    await expect(
      page.getByRole('button', { name: 'Retry Failed' }),
    ).toBeVisible();

    const failedRow = page.locator('p').filter({ hasText: failing.name });
    await expect(failedRow).toBeVisible();
  });
});
