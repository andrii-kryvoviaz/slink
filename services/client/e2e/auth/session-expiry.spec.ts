import { expect, test } from '../fixtures/auth.fixture';

test.describe('Session expiry', () => {
  const protectedRoutes = ['/history', '/profile'];

  for (const route of protectedRoutes) {
    test(`clearing all auth cookies redirects ${route} to login`, async ({
      page,
    }) => {
      await page.context().clearCookies();

      await page.goto(route);

      await expect(page).toHaveURL(/\/profile\/login/);
      await expect(
        page.getByRole('heading', { name: 'Welcome back' }),
      ).toBeVisible();
    });
  }

  test('clearing only sessionId still redirects to login', async ({ page }) => {
    await page.context().clearCookies({ name: 'sessionId' });

    const cookies = await page.context().cookies();
    expect(cookies.some((cookie) => cookie.name === 'sessionId')).toBe(false);
    expect(cookies.some((cookie) => cookie.name === 'refreshToken')).toBe(true);

    await page.goto('/history');

    await expect(page).toHaveURL(/\/profile\/login/);
    await expect(
      page.getByRole('heading', { name: 'Welcome back' }),
    ).toBeVisible();
  });
});
