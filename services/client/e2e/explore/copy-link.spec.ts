import { expect, test } from '../fixtures/auth.fixture';

test.use({
  storageState: 'e2e/.auth/user.json',
  permissions: ['clipboard-read', 'clipboard-write'],
});

test.describe('Image share link copy', () => {
  test('copies the share link from the image info page', async ({
    api,
    page,
    explorePage,
  }) => {
    const imageId = await api.content.uploadImage({ isPublic: false });

    await page.goto(`/info/${imageId}`);

    const copyButton = page.getByRole('button', { name: 'Copy' });
    await expect(copyButton).toBeVisible();

    await explorePage.clickUntil(
      copyButton,
      page.getByRole('button', { name: 'Copied' }),
    );

    await expect(page.getByRole('button', { name: 'Copied' })).toBeVisible();
  });

  test('writes a non-empty value to the clipboard', async ({
    api,
    page,
    explorePage,
  }) => {
    const imageId = await api.content.uploadImage({ isPublic: false });

    await page.goto(`/info/${imageId}`);

    const copyButton = page.getByRole('button', { name: 'Copy' });
    await expect(copyButton).toBeVisible();

    await explorePage.clickUntil(
      copyButton,
      page.getByRole('button', { name: 'Copied' }),
    );

    const clipboardText = await page.evaluate(() =>
      navigator.clipboard.readText(),
    );
    expect(clipboardText.length).toBeGreaterThan(0);
  });
});
