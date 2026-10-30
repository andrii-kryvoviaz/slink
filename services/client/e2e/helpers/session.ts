import {
  type Browser,
  type BrowserContext,
  expect,
  test,
} from '@playwright/test';

import { LoginPage } from '../pages/LoginPage';
import { IconifyCache } from './IconifyCache';
import type { Account } from './accounts';

export function resolveBaseURLFromEnv(): string {
  return process.env.E2E_BASE_URL ?? 'http://localhost:3100';
}

export function resolveBaseURL(): string {
  return test.info().project.use.baseURL ?? resolveBaseURLFromEnv();
}

export async function signInContext(
  browser: Browser,
  account: Account,
): Promise<BrowserContext> {
  const baseURL = resolveBaseURL();
  const context = await browser.newContext({
    storageState: undefined,
    baseURL,
  });
  await IconifyCache.attach(context);

  await context.request.post(LoginPage.URL, {
    form: { username: account.username, password: account.password },
    headers: { origin: new URL(baseURL).origin, accept: 'text/html' },
    maxRedirects: 0,
  });

  const cookieNames = (await context.cookies()).map((cookie) => cookie.name);
  expect(cookieNames, `sign-in of ${account.username}`).toContain('sessionId');

  return context;
}
