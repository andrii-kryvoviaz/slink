import { expect, test } from '../fixtures/auth.fixture';

const SHARE_LINK_PATTERN = /\/i\/[^/?#]+/;

test.use({
  permissions: ['clipboard-read', 'clipboard-write'],
});

test.describe('Image share link copy', () => {
  test('owner copies the share link, which stays copied until the 2000ms reset', async ({
    api,
    page,
    imageInfoPage,
  }) => {
    const imageId = await api.content.uploadImage({ isPublic: false });

    await imageInfoPage.gotoWithPausedClock(async () => {
      await imageInfoPage.goto(imageId);
      await expect(imageInfoPage.shareLinkInput).toHaveValue(
        SHARE_LINK_PATTERN,
      );
      await imageInfoPage.copyShareLinkButton.click();
      await expect(imageInfoPage.copiedShareLinkButton).toBeVisible();
      await expect(imageInfoPage.copyShareLinkButton).toBeEnabled();
      await page.evaluate(() => navigator.clipboard.writeText(''));
    });

    await imageInfoPage.copyShareLinkButton.click();
    await expect(imageInfoPage.copiedShareLinkButton).toBeDisabled();

    expect(await page.evaluate(() => navigator.clipboard.readText())).toBe(
      await imageInfoPage.shareLinkInput.inputValue(),
    );

    await page.clock.runFor(1999);
    await expect(imageInfoPage.copiedShareLinkButton).toBeDisabled();

    await page.clock.runFor(1);
    await expect(imageInfoPage.copyShareLinkButton).toBeEnabled();
  });

  test('owner sees an error toast and no copied state when the clipboard write fails', async ({
    api,
    page,
    imageInfoPage,
  }) => {
    await page.addInitScript(() => {
      Clipboard.prototype.writeText = () =>
        Promise.reject(new Error('Clipboard write denied'));
    });

    const imageId = await api.content.uploadImage({ isPublic: false });

    await imageInfoPage.goto(imageId);
    await expect(imageInfoPage.shareLinkInput).toHaveValue(SHARE_LINK_PATTERN);

    await imageInfoPage.copyShareLinkButton.click();

    await expect(await imageInfoPage.waitForToast()).toContainText(
      'Something went wrong',
    );
    await expect(imageInfoPage.copiedShareLinkButton).toHaveCount(0);
    await expect(imageInfoPage.copyShareLinkButton).toBeEnabled();
  });
});
