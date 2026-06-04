import { type Browser, type BrowserContext, test } from '@playwright/test';

import { LoginPage } from '../pages/LoginPage';
import { ApiClient } from './api';
import { ensureUser, grantRole } from './slink';
import type { TestUser } from './testUsers';

export async function provisionUser(
  user: TestUser,
  opts?: { admin?: boolean },
): Promise<ApiClient> {
  ensureUser({ ...user, active: true });

  if (opts?.admin) {
    grantRole(user.email, 'ROLE_ADMIN');
  }

  const api = await ApiClient.createForUser(user.username, user.password);
  await api.preferences.updatePreferences({ 'display.language': 'en' });

  return api;
}

export async function signInContext(
  browser: Browser,
  user: TestUser,
): Promise<BrowserContext> {
  const context = await browser.newContext({
    storageState: undefined,
    baseURL:
      test.info().project.use.baseURL ??
      process.env.E2E_BASE_URL ??
      'http://localhost:3100',
  });

  const page = await context.newPage();
  const loginPage = new LoginPage(page);
  await loginPage.login(user.username, user.password);
  await page.waitForURL((url) => !url.pathname.includes('/profile/login'), {
    timeout: 15000,
  });
  await page.close();

  return context;
}
