import { expect, test } from '../fixtures/auth.fixture';

test.use({
  permissions: ['clipboard-read', 'clipboard-write'],
});

test.describe('History copy image content', () => {
  test('copies the image bytes of a private image for its owner', async ({
    api,
    page,
    historyPage,
  }) => {
    const imageId = await api.content.uploadImage({ isPublic: false });

    const publicEndpointFailures: string[] = [];
    page.on('response', (response) => {
      if (
        response.status() === 404 &&
        response.url().includes('/api/image/public/')
      ) {
        publicEndpointFailures.push(response.url());
      }
    });

    await historyPage.useGridView();
    await historyPage.goto();

    await historyPage.copyImageContent(imageId);

    await expect(async () => {
      expect(await historyPage.clipboardImageTypes()).toContain('image/png');
    }).toPass({ timeout: 15000 });

    expect(publicEndpointFailures).toEqual([]);
  });
});

test.describe('History copy image link', () => {
  test('owner copies the share link from the table, which stays copied until the 1000ms reset', async ({
    api,
    page,
    historyPage,
  }) => {
    const imageId = await api.content.uploadImage({ isPublic: false });
    const share = await api.shares.publishImageShare(imageId);
    const copyButton = historyPage.tableCopyLinkButton(imageId);

    await historyPage.useTableView();
    await historyPage.gotoWithPausedClock(async () => {
      await historyPage.goto();
      await expect(copyButton).toBeEnabled();
    });

    await copyButton.click();
    await page.clock.runFor(300);

    await expect
      .poll(() => page.evaluate(() => navigator.clipboard.readText()))
      .toBe(share.shareUrl);
    await expect(copyButton).toBeDisabled();

    await page.clock.runFor(999);
    await expect(copyButton).toBeDisabled();

    await page.clock.runFor(1);
    await expect(copyButton).toBeEnabled();
  });

  test('owner sees one error toast and no copied state when the clipboard write fails', async ({
    api,
    page,
    historyPage,
  }) => {
    await page.addInitScript(() => {
      Clipboard.prototype.writeText = () =>
        Promise.reject(new Error('Clipboard write denied'));
    });

    const imageId = await api.content.uploadImage({ isPublic: false });
    const copyButton = historyPage.tableCopyLinkButton(imageId);
    const errorToasts = page
      .locator('[data-sonner-toast]')
      .filter({ hasText: 'Something went wrong' });

    await historyPage.useTableView();
    await historyPage.gotoWithPausedClock(async () => {
      await historyPage.goto();
      await expect(copyButton).toBeEnabled();
    });

    await copyButton.click();
    await page.clock.runFor(300);

    await expect(errorToasts).toHaveCount(1);
    await expect(copyButton).toBeEnabled();
  });
});
