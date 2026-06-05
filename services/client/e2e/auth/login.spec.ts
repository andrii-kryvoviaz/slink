import { expect, test } from '../fixtures/auth.fixture';
import { ensureUser } from '../helpers/provisioning';

test.describe('Login', { tag: '@anonymous' }, () => {
  test('displays the login page', async ({ page }) => {
    await page.goto('/profile/login');

    await expect(
      page.getByRole('heading', { name: 'Welcome back' }),
    ).toBeVisible();
    await expect(
      page.getByPlaceholder('Enter your email or username'),
    ).toBeVisible();
    await expect(page.getByPlaceholder('Enter your password')).toBeVisible();
    await expect(page.getByRole('button', { name: 'Sign In' })).toBeVisible();
  });

  test('logs in with valid credentials', async ({ loginPage, testUser }) => {
    await loginPage.login(testUser.username, testUser.password);
    await loginPage.page.waitForURL(
      (url) => !url.pathname.startsWith('/profile/login'),
      { timeout: 30000 },
    );
  });

  test('rejects login for invalid credentials', async ({ loginPage }) => {
    await loginPage.login('wrong@user.com', 'wrongpassword');
    await loginPage.expectRejected();
  });

  test('shows validation errors for empty fields', async ({ loginPage }) => {
    await loginPage.goto();
    await loginPage.submitButton.click();
    await expect(loginPage.page).toHaveURL(/\/profile\/login/);
  });

  test('rejects login for inactive user', async ({ loginPage }) => {
    ensureUser({
      email: 'inactive@test.local',
      username: 'inactive-login',
      password: 'Test123!',
    });

    await loginPage.login('inactive-login', 'Test123!');
    await loginPage.expectRejected();
  });

  test('rejects login for suspended user', async ({ loginPage, api }) => {
    ensureUser({
      email: 'suspended@test.local',
      username: 'suspended-login',
      password: 'Test123!',
      active: true,
    });

    const user = await api.users.findUserByEmail('suspended@test.local');
    await api.users.changeUserStatus(user.id, 'suspended');

    await loginPage.login('suspended-login', 'Test123!');
    await loginPage.expectRejected();
  });

  test('navigates to signup page from login page', async ({ page }) => {
    await page.goto('/profile/login');

    const signUpLink = page.getByRole('link', { name: 'Sign Up' });
    await expect(signUpLink).toBeVisible();
    await signUpLink.click();
    await expect(page).toHaveURL(/\/profile\/signup/);
  });
});
