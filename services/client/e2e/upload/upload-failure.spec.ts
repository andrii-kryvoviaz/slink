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

  test('single upload shows server-error message when a chunk fails', async ({
    uploadPage,
    page,
  }) => {
    await mockInitSuccess(page);

    await page.route(CHUNK_URL, async (route) => {
      if (route.request().method() !== 'PUT') {
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

    await uploadPage.uploadSingleImage();

    const toast = await uploadPage.waitForToast();
    await expect(toast).toContainText(/internal server error/i);

    await expect(uploadPage.successHeading).toBeHidden();
    await expect(uploadPage.heading).toBeVisible();
    await expect(uploadPage.fileInput).toBeAttached();
  });

  test('single upload shows connection-interrupted message on network abort', async ({
    uploadPage,
    page,
  }) => {
    await mockInitSuccess(page);

    await page.route(CHUNK_URL, async (route) => {
      if (route.request().method() !== 'PUT') {
        await route.continue();
        return;
      }

      await route.abort('connectionreset');
    });

    await uploadPage.goto();
    await expect(uploadPage.heading).toBeVisible();

    await uploadPage.uploadSingleImage();

    const toast = await uploadPage.waitForToast();
    await expect(toast).toContainText(/connection interrupted/i);

    await expect(uploadPage.successHeading).toBeHidden();
    await expect(uploadPage.heading).toBeVisible();
  });

  test('single upload shows session-expired message on 410', async ({
    uploadPage,
    page,
  }) => {
    await mockInitSuccess(page);

    await page.route(CHUNK_URL, async (route) => {
      if (route.request().method() !== 'PUT') {
        await route.continue();
        return;
      }

      await route.fulfill({
        status: 410,
        contentType: 'application/json',
        body: JSON.stringify({ error: { message: 'Gone' } }),
      });
    });

    await uploadPage.goto();
    await expect(uploadPage.heading).toBeVisible();

    await uploadPage.uploadSingleImage();

    const toast = await uploadPage.waitForToast();
    await expect(toast).toContainText(/session expired/i);

    await expect(uploadPage.successHeading).toBeHidden();
    await expect(uploadPage.heading).toBeVisible();
  });

  test('single upload shows timed-out message when the chunk request hangs', async ({
    uploadPage,
    page,
  }) => {
    await page.clock.install();
    await mockInitSuccess(page);

    await page.route(CHUNK_URL, async (route) => {
      if (route.request().method() !== 'PUT') {
        await route.continue();
        return;
      }

      await new Promise(() => {});
    });

    await uploadPage.goto();
    await uploadPage.uploadSingleImage();

    await page.waitForRequest(
      (request) => CHUNK_URL.test(request.url()) && request.method() === 'PUT',
    );

    await page.clock.runFor(31_000);

    await expect(uploadPage.getToast()).toContainText(/timed out/i);

    await expect(uploadPage.successHeading).toBeHidden();
    await expect(uploadPage.heading).toBeVisible();
  });

  test('batch upload recovers after retrying a failed file', async ({
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

    const failedRow = page.locator('p').filter({ hasText: failing.name });
    await expect(failedRow).toBeVisible();
    await expect(
      page.getByText(
        /internal server error|connection interrupted|upload failed/i,
      ),
    ).toBeVisible();

    await page.unroute(/\/api\/upload\/chunked$/);

    await page.getByRole('button', { name: 'Retry Failed' }).click();

    await expect(page.getByRole('button', { name: 'Retry Failed' })).toBeHidden(
      { timeout: 30000 },
    );
    await expect(page.getByRole('button', { name: 'Done' })).toBeVisible({
      timeout: 30000,
    });
    await expect(page.getByText('1 failed', { exact: false })).toBeHidden();
  });
});
