import { expect, test } from '../fixtures/auth.fixture';

test.describe(
  'Password protected collection share',
  { tag: '@anonymous' },
  () => {
    const password = 'SharePass123!';

    test('reroutes a protected collection to the locked page and unlocks with the correct password', async ({
      page,
      sharePage,
      api,
    }) => {
      const imageId = await api.content.uploadImage({ isPublic: false });
      const name = `Locked collection ${Date.now()}`;
      const collectionId = await api.content.createCollection({ name });
      await api.content.addImageToCollection(collectionId, imageId);

      const share = await api.shares.createCollectionShare(collectionId);
      await api.shares.setSharePassword(share.shareId, password);
      await api.shares.publishShare(share.shareId);

      await expect
        .poll(
          async () => {
            const response = await page.request.get(
              `/api/collection/${collectionId}`,
            );
            return response.status();
          },
          { timeout: 15000 },
        )
        .toBe(423);

      await page.goto(`/collection/${collectionId}`);
      await expect(sharePage.protectedHeading).toBeVisible();

      await sharePage.unlock('WrongPassword1!');
      const toast = await sharePage.waitForToast();
      await expect(toast).toContainText('Incorrect password');
      await expect(sharePage.protectedHeading).toBeVisible();

      await sharePage.unlock(password);
      await expect(sharePage.protectedHeading).toBeHidden();

      await expect(
        page.getByRole('heading', { name, exact: true }),
      ).toBeVisible({ timeout: 15000 });
    });
  },
);
