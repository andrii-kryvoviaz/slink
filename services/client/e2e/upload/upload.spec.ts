import { expect, test } from '../fixtures/auth.fixture';

test.describe('Upload', () => {
  test('uploads an image and navigates to the image page', async ({
    uploadPage,
    page,
  }) => {
    await uploadPage.goto();
    await expect(uploadPage.heading).toBeVisible();

    await uploadPage.uploadUniqueImage();
    await uploadPage.waitForUploadComplete();

    await expect(page).toHaveURL(/\/info\//);
  });

  test('uploaded image appears in history', async ({ uploadPage, page }) => {
    await uploadPage.goto();
    await uploadPage.uploadUniqueImage();
    await uploadPage.waitForUploadComplete();

    await page.goto('/history');
    await expect(
      page.getByRole('heading', { name: 'Upload History' }),
    ).toBeVisible();
    await expect(page.locator('a[href^="/info/"]').first()).toBeVisible();
  });

  test('Space opens the file chooser on the dropzone', async ({
    uploadPage,
    page,
  }) => {
    await uploadPage.goto();
    await expect(uploadPage.heading).toBeVisible();
    await page.waitForLoadState('networkidle');

    const chooserPromise = page.waitForEvent('filechooser');
    await uploadPage.dropzone.focus();
    await page.keyboard.press('Space');
    await chooserPromise;
  });
});
