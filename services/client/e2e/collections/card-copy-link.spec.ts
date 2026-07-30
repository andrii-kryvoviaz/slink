import { expect, test } from '../fixtures/auth.fixture';

test.use({
  permissions: ['clipboard-read', 'clipboard-write'],
});

test.describe('Collection card copy link', () => {
  test('lets the owner copy a share link from an owned collection image', async ({
    api,
    page,
  }) => {
    const imageId = await api.content.uploadImage({ isPublic: true });
    const collectionId = await api.content.createCollection({
      name: `E2E Copy ${Date.now()}`,
    });
    await api.content.addImageToCollection(collectionId, imageId);

    await page.goto(`/collection/${collectionId}`);

    const card = page
      .locator('main [role="button"][tabindex="0"]')
      .filter({ has: page.locator('img') })
      .first();
    await card.waitFor({ state: 'visible' });
    await card.hover();

    const copyButton = card.getByRole('button', {
      name: 'Copy link',
      exact: true,
    });
    await expect(copyButton).toBeVisible();

    const shared = page.waitForResponse(
      (response) =>
        response.request().method() === 'POST' &&
        response.url().includes(`/api/image/${imageId}/share`) &&
        response.ok(),
    );
    const published = page.waitForResponse(
      (response) =>
        response.request().method() === 'PUT' &&
        response.url().includes('/api/share/') &&
        response.url().endsWith('/publish') &&
        response.ok(),
    );

    await copyButton.click();
    await shared;
    await published;

    await expect(card.getByRole('button', { name: 'Copied' })).toBeVisible();

    const clipboardText = await page.evaluate(() =>
      navigator.clipboard.readText(),
    );
    expect(clipboardText.length).toBeGreaterThan(0);
  });
});
