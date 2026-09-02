import { expect, test } from '../fixtures/auth.fixture';

const EXPIRY_LABEL = /^\s*(\d+h|\d+d|\d+w|\d+mo|\d+y|Expired|—)\s*$/;

const TWELVE_DAYS_MS = 12 * 24 * 60 * 60 * 1000;
const THREE_HOURS_MS = 3 * 60 * 60 * 1000;
const SHORT_EXPIRY_MS = 10 * 1000;

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

  test('labels sub-day expiry in hours', async ({ api, sharesPage }) => {
    const imageId = await api.content.uploadImage({ isPublic: true });
    const share = await api.shares.publishImageShare(imageId);
    await api.shares.setShareExpiration(
      share.shareId,
      new Date(Date.now() + THREE_HOURS_MS).toISOString(),
    );

    await sharesPage.goto();
    await expect(sharesPage.heading).toBeVisible();

    const row = sharesPage.rowForImage(imageId);
    await expect(row).toHaveCount(1);

    await expect(await sharesPage.expiresCell(row)).toHaveText(/^\s*\d+h\s*$/);
  });

  test('marks an expired share and dims its row', async ({
    api,
    sharesPage,
  }) => {
    const imageId = await api.content.uploadImage({ isPublic: true });
    const share = await api.shares.publishImageShare(imageId);
    await api.shares.setShareExpiration(
      share.shareId,
      new Date(Date.now() + SHORT_EXPIRY_MS).toISOString(),
    );

    const row = sharesPage.rowForImage(imageId);

    await expect(async () => {
      await sharesPage.goto();
      await expect(sharesPage.heading).toBeVisible();
      await expect(row).toHaveCount(1);
      await expect(await sharesPage.expiresCell(row)).toHaveText('Expired', {
        timeout: 2000,
      });
    }).toPass({ timeout: 60000 });

    await expect(row).toHaveClass(/opacity-60/);
  });

  test('renders a placeholder when a share never expires', async ({
    api,
    sharesPage,
  }) => {
    const imageId = await api.content.uploadImage({ isPublic: true });
    await api.shares.publishImageShare(imageId);

    await sharesPage.goto();
    await expect(sharesPage.heading).toBeVisible();

    const row = sharesPage.rowForImage(imageId);
    await expect(row).toHaveCount(1);

    await expect(await sharesPage.expiresCell(row)).toHaveText('—');
  });
});
