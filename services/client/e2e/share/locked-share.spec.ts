import { expect, test } from '../fixtures/auth.fixture';

test.describe('Password protected share', () => {
  test.use({ storageState: { cookies: [], origins: [] } });

  const password = 'SharePass123!';

  test('reroutes a protected image to the locked page and unlocks with the correct password', async ({
    page,
    sharePage,
    contentApi,
  }) => {
    const imageId = await contentApi.uploadImage({ isPublic: false });
    const share = await contentApi.createImageShare(imageId);
    await contentApi.setSharePassword(share.shareId, password);
    await contentApi.publishShare(share.shareId);

    await expect
      .poll(
        async () => {
          const response = await page.request.get(`/api/image/${imageId}.png`);
          return response.status();
        },
        { timeout: 15000 },
      )
      .toBe(423);

    await page.goto(`/image/${imageId}.png`);
    await expect(sharePage.protectedHeading).toBeVisible();

    await sharePage.unlock('WrongPassword1!');
    const toast = await sharePage.waitForToast();
    await expect(toast).toContainText('Incorrect password');
    await expect(sharePage.protectedHeading).toBeVisible();

    await sharePage.unlock(password);
    await expect(sharePage.protectedHeading).toBeHidden();

    await expect
      .poll(async () => {
        const response = await page.request.get(`/api/image/${imageId}.png`);
        return response.status();
      })
      .toBe(200);
  });
});
