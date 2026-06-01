import { expect, test } from '../fixtures/auth.fixture';

test.describe('Create collection', () => {
  test.use({ storageState: 'e2e/.auth/user.json' });

  test('creates a collection and shows it in the list', async ({
    collectionsPage,
  }) => {
    const name = `E2E Col ${Date.now()}`;

    await collectionsPage.goto();
    await expect(collectionsPage.heading).toBeVisible();

    await collectionsPage.createCollection(name);

    await expect(collectionsPage.createDialog).toBeHidden();
    await expect(
      collectionsPage.page.getByText(name, { exact: false }).first(),
    ).toBeVisible();
  });
});
