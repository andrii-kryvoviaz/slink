import { expect, test } from '../fixtures/auth.fixture';

test.describe('Logout', () => {
  test.describe.configure({ mode: 'serial' });
  test.use({ storageState: 'e2e/.auth/user.json' });

  test('logs out and redirects to login', async ({ page }) => {
    await page.goto('/');

    await page.evaluate(() => {
      const form = document.createElement('form');
      form.method = 'POST';
      form.action = '/profile/logout';
      document.body.appendChild(form);
      form.requestSubmit();
    });

    await expect(page).toHaveURL(/\/profile\/login/);
  });

  test('clears session and prevents accessing protected pages after logout', async ({
    page,
  }) => {
    await page.goto('/');

    await page.evaluate(() => {
      const form = document.createElement('form');
      form.method = 'POST';
      form.action = '/profile/logout';
      document.body.appendChild(form);
      form.requestSubmit();
    });

    await expect(page).toHaveURL(/\/profile\/login/);

    await page.goto('/profile');
    await expect(page).toHaveURL(/\/profile\/login/);
  });

  test('shows login page after logout with heading visible', async ({
    page,
  }) => {
    await page.goto('/');

    await page.evaluate(() => {
      const form = document.createElement('form');
      form.method = 'POST';
      form.action = '/profile/logout';
      document.body.appendChild(form);
      form.requestSubmit();
    });

    await expect(page).toHaveURL(/\/profile\/login/);
    await expect(
      page.getByRole('heading', { name: 'Welcome back' }),
    ).toBeVisible();
  });

  test('allows re-login after logout', async ({
    page,
    loginPage,
    testUser,
  }) => {
    await page.goto('/');

    await page.evaluate(() => {
      const form = document.createElement('form');
      form.method = 'POST';
      form.action = '/profile/logout';
      document.body.appendChild(form);
      form.requestSubmit();
    });

    await expect(page).toHaveURL(/\/profile\/login/);

    await loginPage.login(testUser.username, testUser.password);
    await loginPage.page.waitForURL(
      (url) => !url.pathname.startsWith('/profile/login'),
      { timeout: 30000 },
    );
  });
});
