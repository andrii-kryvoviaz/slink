import { expect, test } from '../fixtures/auth.fixture';

test.describe('Protected route guard', () => {
  test.use({ storageState: { cookies: [], origins: [] } });

  const protectedRoutes = ['/history', '/collections', '/shares'];

  for (const route of protectedRoutes) {
    test(`redirects anonymous visitor from ${route} to login`, async ({
      page,
    }) => {
      await page.goto(route);
      await expect(page).toHaveURL(/\/profile\/login/);
      await expect(
        page.getByRole('heading', { name: 'Welcome back' }),
      ).toBeVisible();
    });
  }
});
