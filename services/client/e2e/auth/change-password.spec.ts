import type { BrowserContext } from '@playwright/test';

import { expect, test } from '../fixtures/auth.fixture';
import { provisionUser, signInContext } from '../helpers/auth';
import { type TestUser, uniqueUser } from '../helpers/testUsers';
import { LoginPage } from '../pages/LoginPage';
import { ProfilePage } from '../pages/ProfilePage';

const NEW_PASSWORD = 'NewE2ePass456!';

test.describe('Change password', { tag: '@anonymous' }, () => {
  test.describe.configure({ mode: 'serial' });

  test('rejects a wrong current password and keeps the original usable', async ({
    browser,
  }) => {
    const user = uniqueUser('pwd-wrong');
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
    const user = uniqueUser('pwd-happy');
    await provisionUser(user);
    const context: BrowserContext = await signInContext(browser, user);
    const page = await context.newPage();

    const profilePage = new ProfilePage(page);
    await profilePage.goto();
    await profilePage.changePassword(user.password, NEW_PASSWORD);

    const toast = await profilePage.waitForToast();
    await expect(toast).toContainText('Password changed successfully');

    await context.close();

    const updated: TestUser = { ...user, password: NEW_PASSWORD };

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
