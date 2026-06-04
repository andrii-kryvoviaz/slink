import { expect, test } from '../fixtures/auth.fixture';
import { ensureUser, grantRole } from '../helpers/slink';
import { uniqueUser } from '../helpers/testUsers';
import { LoginPage } from '../pages/LoginPage';

test.describe(
  'Role grant reflected after re-login',
  { tag: '@anonymous' },
  () => {
    test.describe.configure({ mode: 'serial' });

    test('hides the admin group until the user logs in again', async ({
      browser,
    }) => {
      const user = uniqueUser('rolegrant');
      ensureUser({ ...user, active: true });

      const context = await browser.newContext({ storageState: undefined });
      const page = await context.newPage();
      const loginPage = new LoginPage(page);

      await loginPage.login(user.username, user.password);
      await page.waitForURL(
        (url) => !url.pathname.startsWith('/profile/login'),
        { timeout: 30000 },
      );

      await page.goto('/');
      await expect(page.getByRole('link', { name: 'Explore' })).toBeVisible();
      await expect(page.getByRole('link', { name: 'Dashboard' })).toHaveCount(
        0,
      );
      await expect(page.getByRole('link', { name: 'Users' })).toHaveCount(0);

      grantRole(user.email, 'ROLE_ADMIN');

      await page.reload();
      await expect(page.getByRole('link', { name: 'Dashboard' })).toHaveCount(
        0,
      );
      await expect(page.getByRole('link', { name: 'Users' })).toHaveCount(0);

      await page.evaluate(() => {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/profile/logout';
        document.body.appendChild(form);
        form.requestSubmit();
      });
      await expect(page).toHaveURL(/\/profile\/login/);

      await loginPage.login(user.username, user.password);
      await page.waitForURL(
        (url) => !url.pathname.startsWith('/profile/login'),
        { timeout: 30000 },
      );

      await page.goto('/');
      await expect(page.getByRole('link', { name: 'Dashboard' })).toBeVisible();
      await expect(page.getByRole('link', { name: 'Users' })).toBeVisible();

      await context.close();
    });
  },
);
