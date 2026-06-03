import { expect, test } from '../fixtures/auth.fixture';

test.describe('Admin navigation', () => {
  test.describe('as admin user', () => {
    test('shows the Administration group in the sidebar', async ({ page }) => {
      await page.goto('/');

      await expect(page.getByRole('link', { name: 'Dashboard' })).toBeVisible();
      await expect(page.getByRole('link', { name: 'Users' })).toBeVisible();
    });
  });

  test.describe('as regular user', { tag: '@anonymous' }, () => {
    test.describe.configure({ mode: 'serial' });

    test('hides admin navigation for a non-admin user', async ({
      page,
      signupPage,
      loginPage,
      api,
    }) => {
      const suffix = Date.now();
      const username = `nonadmin${suffix}`;
      const email = `${username}@example.com`;
      const password = 'TestPass123!';

      await signupPage.signup({
        username,
        email,
        password,
        confirm: password,
      });
      await expect(signupPage.page).not.toHaveURL(/\/profile\/signup/);

      const user = await api.users.findUserByEmail(email);
      await api.users.changeUserStatus(user.id, 'active');

      await loginPage.login(username, password);
      await loginPage.page.waitForURL(
        (url) => !url.pathname.startsWith('/profile/login'),
        { timeout: 30000 },
      );

      await page.goto('/');
      await expect(page.getByRole('link', { name: 'Explore' })).toBeVisible();
      await expect(page.getByRole('link', { name: 'Dashboard' })).toHaveCount(
        0,
      );
      await expect(page.getByRole('link', { name: 'Users' })).toHaveCount(0);
    });
  });
});
