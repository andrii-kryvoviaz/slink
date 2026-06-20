import { expect, test } from '../fixtures/auth.fixture';
import { createUniquePng } from '../helpers/uniqueImage';

test.describe('Upload deduplication', () => {
  test('shows the duplicate toast with a link when re-uploading the same image', async ({
    uploadPage,
    page,
  }) => {
    const { buffer, name } = createUniquePng();

    await uploadPage.goto();
    await uploadPage.uploadBuffer({ name, mimeType: 'image/png', buffer });
    await uploadPage.waitForUploadComplete();
    await expect(page).toHaveURL(/\/info\//);

    const firstId = page.url().split('/info/')[1].split(/[/?#]/)[0];

    await uploadPage.goto();
    await uploadPage.uploadBuffer({
      name: `duplicate-${name}`,
      mimeType: 'image/png',
      buffer,
    });

    await expect(page).toHaveURL(/\/upload/);

    const duplicateToast = page
      .locator('[data-sonner-toast]')
      .filter({ hasText: 'Image Already Exists' });
    await expect(duplicateToast).toBeVisible();

    const rawErrorToast = page
      .locator('[data-sonner-toast]')
      .filter({ hasText: /Image already exists:\s*[0-9a-f-]{8,}/i });
    await expect(rawErrorToast).toHaveCount(0);

    const viewButton = duplicateToast.getByRole('button', {
      name: /view image/i,
    });
    await expect(viewButton).toBeVisible();
    await viewButton.click();
    await expect(page).toHaveURL(new RegExp(`/info/${firstId}`));
  });
});
