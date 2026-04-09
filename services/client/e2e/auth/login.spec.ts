import { execFileSync } from 'child_process';

import { expect, test } from '../fixtures/auth.fixture';

const DOCKER_ARGS = [
  'compose',
  '-p',
  'slink-e2e',
  'exec',
  '-T',
  'slink',
  'slink',
];

function slink(...args: string[]) {
  execFileSync('docker', [...DOCKER_ARGS, ...args]);
}

test.describe('Login', () => {
  test.use({ storageState: { cookies: [], origins: [] } });

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

  test('shows error for invalid credentials', async ({ loginPage }) => {
    await loginPage.login('wrong@user.com', 'wrongpassword');
    await loginPage.waitForToast();
    await expect(loginPage.page).toHaveURL(/\/profile\/login/);
  });

  test('shows validation errors for empty fields', async ({ loginPage }) => {
    await loginPage.goto();
    await loginPage.submitButton.click();
    await expect(loginPage.page).toHaveURL(/\/profile\/login/);
  });

  test('rejects login for inactive user', async ({ loginPage }) => {
    try {
      slink(
        'user:create',
        '--email=inactive@test.local',
        '--username=inactive-login',
        '-p',
        'Test123!',
      );
    } catch {}

    await loginPage.login('inactive-login', 'Test123!');
    await loginPage.waitForToast();
    await expect(loginPage.page).toHaveURL(/\/profile\/login/);
  });

  test('rejects login for suspended user', async ({ loginPage, adminApi }) => {
    try {
      slink(
        'user:create',
        '--email=suspended@test.local',
        '--username=suspended-login',
        '-p',
        'Test123!',
        '-a',
      );
    } catch {
      slink('user:activate', '--email=suspended@test.local');
    }

    const user = await adminApi.findUserByEmail('suspended@test.local');
    await adminApi.changeUserStatus(user.id, 'suspended');

    await loginPage.login('suspended-login', 'Test123!');
    await loginPage.waitForToast();
    await expect(loginPage.page).toHaveURL(/\/profile\/login/);
  });

  test('navigates to signup page from login page', async ({ page }) => {
    await page.goto('/profile/login');

    const signUpLink = page.getByRole('link', { name: 'Sign Up' });
    await expect(signUpLink).toBeVisible();
    await signUpLink.click();
    await expect(page).toHaveURL(/\/profile\/signup/);
  });
});
