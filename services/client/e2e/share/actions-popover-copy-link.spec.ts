import { expect, test } from '../fixtures/auth.fixture';

test.use({
  permissions: ['clipboard-read', 'clipboard-write'],
});

test.describe('Share actions popover copy link', () => {
  test('owner copies the share url and sees it copied until the 1500ms reset', async ({
    api,
    page,
    sharesPage,
  }) => {
    const imageId = await api.content.uploadImage({ isPublic: true });
    const share = await api.shares.publishImageShare(imageId);
    const row = sharesPage.rowForImage(imageId);
    const copiedLabel = sharesPage.copyLinkMenuItem.getByText(
      'Copied to clipboard',
    );

    await sharesPage.gotoWithPausedClock(async () => {
      await sharesPage.goto();
      await expect(row).toHaveCount(1);
    });

    await sharesPage.clickUntilOnPausedClock(
      sharesPage.actionsTriggerFor(row),
      sharesPage.copyLinkMenuItem,
    );
    await sharesPage.clickUntilOnPausedClock(
      sharesPage.copyLinkMenuItem,
      copiedLabel,
    );

    expect(await page.evaluate(() => navigator.clipboard.readText())).toBe(
      share.shareUrl,
    );

    await page.clock.runFor(1499);
    await expect(copiedLabel).toBeVisible();

    await page.clock.runFor(1);
    await expect(
      sharesPage.copyLinkMenuItem.getByText('Copy the share link'),
    ).toBeVisible();
  });
});
