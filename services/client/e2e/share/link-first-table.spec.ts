import { expect, test } from '../fixtures/auth.fixture';

const EXPIRY_LABEL = /^(\d+h|\d+d|\d+w|\d+mo|\d+y|Expired|—)$/;

const TWELVE_DAYS_MS = 12 * 24 * 60 * 60 * 1000;

test.use({
  permissions: ['clipboard-read', 'clipboard-write'],
});

test.describe('Link-first shares table', () => {
  test('shows the short link, copies the full url and labels expiry', async ({
    api,
    page,
    sharesPage,
  }) => {
    const imageId = await api.content.uploadImage({ isPublic: true });
    const share = await api.shares.publishImageShare(imageId);
    await api.shares.setShareExpiration(
      share.shareId,
      new Date(Date.now() + TWELVE_DAYS_MS).toISOString(),
    );

    await sharesPage.goto();
    await expect(sharesPage.heading).toBeVisible();

    const row = sharesPage.rowForImage(imageId);
    await expect(row).toHaveCount(1);

    await expect(sharesPage.shortLinkFor(row)).toHaveText(
      share.shareUrl.replace(/^https?:\/\//, ''),
    );

    await row.hover();
    await sharesPage.copyLinkButton(row).click();

    await expect(sharesPage.copiedIndicator(row)).toBeVisible();

    const clipboardUrl = await page.evaluate(() =>
      navigator.clipboard.readText(),
    );
    expect(clipboardUrl).toBe(share.shareUrl);

    const expiresCell = await sharesPage.expiresCell(row);
    await expect(expiresCell).toHaveText(EXPIRY_LABEL);
    await expect(expiresCell).not.toHaveText('—');
  });
});
