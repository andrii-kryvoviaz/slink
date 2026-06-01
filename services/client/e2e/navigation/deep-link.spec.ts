import { expect, test } from '../fixtures/auth.fixture';

test.describe('Authenticated deep-link cold load', () => {
  test.use({ storageState: 'e2e/.auth/user.json' });

  test('hard-navigates directly to /history and renders the page', async ({
    page,
  }) => {
    await page.goto('/history', { waitUntil: 'load' });

    await expect(page).toHaveURL(/\/history/);
    await expect(
      page.getByRole('heading', { name: 'Upload History' }),
    ).toBeVisible();
  });

  test('hard-navigates directly to /collections and renders the page', async ({
    page,
  }) => {
    await page.goto('/collections', { waitUntil: 'load' });

    await expect(page).toHaveURL(/\/collections/);
    await expect(
      page.getByRole('heading', { name: 'Collections', exact: true }),
    ).toBeVisible();
  });

  test('hard-navigates directly to a seeded collection detail page', async ({
    page,
    contentApi,
  }) => {
    const name = `Deep Link ${Date.now()}`;
    const collectionId = await contentApi.createCollection({ name });

    await page.goto(`/collection/${collectionId}`, { waitUntil: 'load' });

    await expect(page).toHaveURL(new RegExp(`/collection/${collectionId}`));
    await expect(page.getByText(name, { exact: true }).first()).toBeVisible();
    await expect(page.getByText(/items/).first()).toBeVisible();
  });
});
