import { expect, test } from '../fixtures/auth.fixture';

test.describe('Upload', () => {
  test.use({ storageState: 'e2e/.auth/user.json' });

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
});
