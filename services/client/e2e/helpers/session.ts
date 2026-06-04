import { type Browser, type BrowserContext, test } from '@playwright/test';

import { LoginPage } from '../pages/LoginPage';
import type { Account } from './accounts';

export async function signInContext(
  browser: Browser,
  account: Account,
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
  await loginPage.login(account.username, account.password);
  await page.waitForURL((url) => !url.pathname.includes('/profile/login'), {
    timeout: 15000,
  });
  await page.close();

  return context;
}
