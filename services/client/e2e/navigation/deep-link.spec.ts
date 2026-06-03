import { expect, test } from '../fixtures/auth.fixture';

test.describe('Authenticated deep-link cold load', () => {
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
    api,
  }) => {
    const name = `Deep Link ${Date.now()}`;
    const collectionId = await api.content.createCollection({ name });

    await page.goto(`/collection/${collectionId}`, { waitUntil: 'load' });

    await expect(page).toHaveURL(new RegExp(`/collection/${collectionId}`));
    await expect(page.getByText(name, { exact: true }).first()).toBeVisible();
    await expect(page.getByText(/items/).first()).toBeVisible();
  });
});
