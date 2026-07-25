import type { Page } from '@playwright/test';

import { expect, test } from '../fixtures/auth.fixture';
import { unique } from '../helpers/accounts';
import { provisionUser } from '../helpers/provisioning';
import { signInContext } from '../helpers/session';
import { AdminUsersPage } from '../pages/AdminUsersPage';
import { LoginPage } from '../pages/LoginPage';

const submitLogout = (page: Page) =>
  page.evaluate(() => {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/profile/logout';
    document.body.appendChild(form);
    form.requestSubmit();
  });

test.describe('Admin user moderation', () => {
  test('suspends a user from the admin list and blocks their login', async ({
    page,
    browser,
  }) => {
    const targetUser = unique('suspend');
    await provisionUser(targetUser);

    const adminUsersPage = new AdminUsersPage(page);
    await adminUsersPage.goto();

    await expect(
      await adminUsersPage.badgeInRow(targetUser.username, 'Active'),
    ).toBeVisible();

    await adminUsersPage.runAction(targetUser.username, 'Suspend');

    await expect(
      await adminUsersPage.badgeInRow(targetUser.username, 'Suspended'),
    ).toBeVisible();

    const context = await browser.newContext({ storageState: undefined });
    const loginPage = new LoginPage(await context.newPage());
    await loginPage.login(targetUser.username, targetUser.password);
    await loginPage.expectRejected();
    await context.close();
  });

  test('reactivates a suspended user and restores their login', async ({
    page,
    browser,
    api,
  }) => {
    const targetUser = unique('reactivate');
    await provisionUser(targetUser);

    const record = await api.users.findUserByEmail(targetUser.email);
    await api.users.changeUserStatus(record.id, 'suspended');

    const adminUsersPage = new AdminUsersPage(page);
    await adminUsersPage.goto();

    await expect(
      await adminUsersPage.badgeInRow(targetUser.username, 'Suspended'),
    ).toBeVisible();

    await adminUsersPage.runAction(targetUser.username, 'Activate');

    await expect(
      await adminUsersPage.badgeInRow(targetUser.username, 'Active'),
    ).toBeVisible();

    const context = await browser.newContext({ storageState: undefined });
    const loginPage = new LoginPage(await context.newPage());
    await loginPage.login(targetUser.username, targetUser.password);
    await loginPage.page.waitForURL(
      (url) => !url.pathname.startsWith('/profile/login'),
      { timeout: 30000 },
    );
    await context.close();
  });

  test('revokes admin and drops the admin navigation after re-login', async ({
    page,
    browser,
  }) => {
    const targetUser = unique('revokeadmin');
    await provisionUser(targetUser, { admin: true });

    const targetContext = await signInContext(browser, targetUser);
    const targetPage = await targetContext.newPage();

    await targetPage.goto('/');
    await expect(
      targetPage.getByRole('link', { name: 'Dashboard' }),
    ).toBeVisible();
    await expect(targetPage.getByRole('link', { name: 'Users' })).toBeVisible();

    const adminUsersPage = new AdminUsersPage(page);
    await adminUsersPage.goto();

    await expect(
      await adminUsersPage.badgeInRow(targetUser.username, 'Admin'),
    ).toBeVisible();

    await adminUsersPage.runAction(targetUser.username, 'Remove Admin');

    await expect(
      await adminUsersPage.badgeInRow(targetUser.username, 'User'),
    ).toBeVisible();

    await submitLogout(targetPage);
    await expect(targetPage).toHaveURL(/\/profile\/login/);

    const targetLogin = new LoginPage(targetPage);
    await targetLogin.login(targetUser.username, targetUser.password);
    await targetPage.waitForURL(
      (url) => !url.pathname.startsWith('/profile/login'),
      { timeout: 30000 },
    );

    await targetPage.goto('/');
    await expect(
      targetPage.getByRole('link', { name: 'Explore' }),
    ).toBeVisible();
    await expect(
      targetPage.getByRole('link', { name: 'Dashboard' }),
    ).toHaveCount(0);
    await expect(targetPage.getByRole('link', { name: 'Users' })).toHaveCount(
      0,
    );

    await targetContext.close();
  });
});
