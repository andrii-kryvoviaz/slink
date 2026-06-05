import { expect, test } from '../fixtures/auth.fixture';

test.use({
  permissions: ['clipboard-read', 'clipboard-write'],
});

test.describe('Share publish roundtrip', () => {
  test('owner copies an auto-published link that a visitor can open', async ({
    api,
    page,
    browser,
    imageInfoPage,
  }) => {
    const imageId = await api.content.uploadImage({ isPublic: false });

    const shareLoaded = page.waitForResponse(
      (response) =>
        response.url().includes(`/api/image/${imageId}/share`) && response.ok(),
    );

    await imageInfoPage.goto(imageId);
    await shareLoaded;

    const copyButton = page.getByRole('button', { name: 'Copy', exact: true });
    await expect(copyButton).toBeVisible();
    await expect(copyButton).toBeEnabled();

    await imageInfoPage.clickUntil(
      copyButton,
      page.getByRole('button', { name: 'Copied' }),
    );

    const clipboardUrl = await page.evaluate(() =>
      navigator.clipboard.readText(),
    );
    expect(clipboardUrl).toMatch(/\/i\/[^/?#]+/);

    const context = await browser.newContext({ storageState: undefined });
    const visitor = await context.newPage();

    try {
      const response = await visitor.goto(clipboardUrl);
      expect(response?.status()).toBe(200);
      expect(response?.headers()['content-type']).toContain('image');
      await expect(visitor).toHaveURL(/\/image\/[0-9a-fA-F-]+\.[a-z0-9]+/);
      await expect(visitor).not.toHaveURL(/\/image\/unavailable/);
    } finally {
      await context.close();
    }
  });
});
