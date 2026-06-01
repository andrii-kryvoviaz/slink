import { expect, test } from '../fixtures/auth.fixture';

test.describe('Signup', () => {
  test.use({ storageState: { cookies: [], origins: [] } });

  test('displays the signup page', async ({ page }) => {
    await page.goto('/profile/signup');

    await expect(
      page.getByRole('heading', { name: 'Create Account' }),
    ).toBeVisible();
    await expect(page.getByPlaceholder('Choose a username')).toBeVisible();
    await expect(page.getByPlaceholder('Enter your email')).toBeVisible();
    await expect(
      page.getByPlaceholder('Create a strong password'),
    ).toBeVisible();
    await expect(page.getByPlaceholder('Confirm your password')).toBeVisible();
    await expect(
      page.getByRole('button', { name: 'Create Account' }),
    ).toBeVisible();
  });

  test('shows error for mismatched passwords', async ({ signupPage }) => {
    const suffix = Date.now();

    await signupPage.signup({
      username: `testuser${suffix}`,
      email: `testuser${suffix}@example.com`,
      password: 'TestPass123!',
      confirm: 'DifferentPass456!',
    });

    await expect(
      signupPage.page.getByText('Passwords do not match'),
    ).toBeVisible();
  });

  test('rejects signup with duplicate username', async ({
    signupPage,
    testUser,
  }) => {
    await signupPage.signup({
      username: testUser.username,
      email: `unique${Date.now()}@example.com`,
      password: 'TestPass123!',
      confirm: 'TestPass123!',
    });

    await expect(signupPage.page).toHaveURL(/\/profile\/signup/, {
      timeout: 15000,
    });
    await expect(signupPage.page).not.toHaveURL(/\/profile\/awaiting-approval/);
  });

  test('shows link to login page', async ({ page }) => {
    await page.goto('/profile/signup');

    const banner = page
      .getByText('Already have an account?')
      .locator('xpath=ancestor::*[3]');
    const signInLink = banner.getByRole('link', { name: 'Sign In' });
    await expect(signInLink).toBeVisible();
    await expect(signInLink).toHaveAttribute('href', '/profile/login');
  });

  test('navigates to login page when sign in link is clicked', async ({
    page,
  }) => {
    await page.goto('/profile/signup');

    const banner = page
      .getByText('Already have an account?')
      .locator('xpath=ancestor::*[3]');
    await banner.getByRole('link', { name: 'Sign In' }).click();
    await expect(page).toHaveURL(/\/profile\/login/);
  });

  test('shows awaiting approval page after signup', async ({ signupPage }) => {
    const suffix = Date.now();

    await signupPage.signup({
      username: `approval${suffix}`,
      email: `approval${suffix}@example.com`,
      password: 'TestPass123!',
      confirm: 'TestPass123!',
    });

    await expect(signupPage.page).toHaveURL(/\/profile\/awaiting-approval/);
    await expect(
      signupPage.page.getByRole('heading', { name: 'Review in Progress' }),
    ).toBeVisible();
  });

  test('shows welcome aboard after admin approves user', async ({
    signupPage,
    adminApi,
  }) => {
    const suffix = Date.now();

    await signupPage.signup({
      username: `approve${suffix}`,
      email: `approve${suffix}@example.com`,
      password: 'TestPass123!',
      confirm: 'TestPass123!',
    });

    await expect(signupPage.page).toHaveURL(/\/profile\/awaiting-approval/);

    const accountRef = signupPage.page
      .getByText('Your Account Reference')
      .locator('xpath=ancestor::*[3]');
    const userId = await accountRef.getByRole('button').first().textContent();
    await adminApi.changeUserStatus(userId!.trim(), 'active');

    await signupPage.page.reload();
    await expect(
      signupPage.page.getByRole('heading', { name: 'Welcome Aboard' }),
    ).toBeVisible();
  });

  test('sign in link on awaiting approval page navigates to login', async ({
    signupPage,
    awaitingApprovalPage,
  }) => {
    const suffix = Date.now();

    await signupPage.signup({
      username: `awaitlink${suffix}`,
      email: `awaitlink${suffix}@example.com`,
      password: 'TestPass123!',
      confirm: 'TestPass123!',
    });

    await expect(signupPage.page).toHaveURL(/\/profile\/awaiting-approval/);
    await awaitingApprovalPage.signInLink.click();
    await expect(signupPage.page).toHaveURL(/\/profile\/login/);
  });
});
