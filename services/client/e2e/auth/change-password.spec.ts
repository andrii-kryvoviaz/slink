import type { BrowserContext } from '@playwright/test';

import { expect, test } from '../fixtures/auth.fixture';
import { type Account, unique } from '../helpers/accounts';
import { provisionUser } from '../helpers/provisioning';
import { signInContext } from '../helpers/session';
import { createSsoProvider, deleteSsoProviders } from '../helpers/ssoProviders';
import { LoginPage } from '../pages/LoginPage';
import { ProfilePage } from '../pages/ProfilePage';

const NEW_PASSWORD = 'NewE2ePass456!';
const PREFIX = 'e2e-pwd-local-auth';

test.describe('Change password', { tag: '@anonymous' }, () => {
  test.describe.configure({ mode: 'serial' });

  test('rejects a wrong current password and keeps the original usable', async ({
    browser,
  }) => {
    const user = unique('pwd-wrong');
    await provisionUser(user);
    const context = await signInContext(browser, user);
    const page = await context.newPage();

    const profilePage = new ProfilePage(page);
    await profilePage.goto();
    await profilePage.changePassword('WrongPassword123!', NEW_PASSWORD);

    await expect(profilePage.oldPasswordError()).toHaveText(
      'Invalid old password provided.',
    );

    await context.close();

    const loginCheck = await browser.newContext({ storageState: undefined });
    const loginPage = new LoginPage(await loginCheck.newPage());
    await loginPage.login(user.username, user.password);
    await loginPage.page.waitForURL(
      (url) => !url.pathname.startsWith('/profile/login'),
      { timeout: 30000 },
    );
    await loginCheck.close();
  });

  test('changes the password and rotates the valid credentials', async ({
    browser,
  }) => {
    const user = unique('pwd-happy');
    await provisionUser(user);
    const context: BrowserContext = await signInContext(browser, user);
    const page = await context.newPage();

    const profilePage = new ProfilePage(page);
    await profilePage.goto();
    await profilePage.changePassword(user.password, NEW_PASSWORD);

    const toast = await profilePage.waitForToast();
    await expect(toast).toContainText('Password changed successfully');

    await context.close();

    const updated: Account = { ...user, password: NEW_PASSWORD };

    const newContext = await browser.newContext({ storageState: undefined });
    const newLogin = new LoginPage(await newContext.newPage());
    await newLogin.login(updated.username, updated.password);
    await newLogin.page.waitForURL(
      (url) => !url.pathname.startsWith('/profile/login'),
      { timeout: 30000 },
    );
    await newContext.close();

    const staleContext = await browser.newContext({ storageState: undefined });
    const staleLogin = new LoginPage(await staleContext.newPage());
    await staleLogin.login(user.username, user.password);
    await staleLogin.expectRejected();
    await staleContext.close();
  });
});

test.describe(
  'Change password with local auth off',
  { tag: ['@serial', '@anonymous', '@sc-1134'] },
  () => {
    test.afterEach(({ api }) => deleteSsoProviders(api, PREFIX));

    test('the profile keeps its settings and offers no password change', async ({
      browser,
      api,
      settingsApi,
    }) => {
      const user = unique('pwd-sso-only');
      await provisionUser(user);
      const context = await signInContext(browser, user);
      const page = await context.newPage();

      await createSsoProvider(api, PREFIX);
      await settingsApi.set('user', { localAuthEnabled: false });

      const profilePage = new ProfilePage(page);
      await page.goto(ProfilePage.URL);

      await expect(
        page.getByRole('heading', { name: 'Profile Settings' }),
      ).toBeVisible();
      await expect(profilePage.oldPasswordInput).toHaveCount(0);
      await expect(profilePage.newPasswordInput).toHaveCount(0);
      await expect(profilePage.confirmPasswordInput).toHaveCount(0);
      await expect(profilePage.updatePasswordButton).toHaveCount(0);

      await context.close();
    });
  },
);
