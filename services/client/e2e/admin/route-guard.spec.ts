import type { BrowserContext, Page } from '@playwright/test';

import { expect, test } from '../fixtures/auth.fixture';
import { unique } from '../helpers/accounts';
import { provisionUser } from '../helpers/provisioning';
import { signInContext } from '../helpers/session';

const ADMIN_ROUTES = [
  '/admin/dashboard',
  '/admin/user',
  '/admin/settings/customization',
  '/admin/settings/image',
  '/admin/settings/security',
  '/admin/settings/sso',
  '/admin/settings/storage',
];

test.describe('Admin route guard', () => {
  test.describe('as anonymous visitor', { tag: '@anonymous' }, () => {
    for (const route of ADMIN_ROUTES) {
      test(`redirects anonymous visitor from ${route} to login`, async ({
        page,
      }) => {
        await page.goto(route);

        await expect(page).toHaveURL(/\/profile\/login/);
        await expect(
          page.getByRole('heading', { name: 'Welcome back' }),
        ).toBeVisible();
      });
    }
  });

  test.describe('as signed-in non-admin user', () => {
    test.describe.configure({ mode: 'serial' });

    let context: BrowserContext;
    let page: Page;

    test.beforeAll(async ({ browser }) => {
      const account = unique('guard');
      await provisionUser(account);

      context = await signInContext(browser, account);
      page = await context.newPage();
    });

    test.afterAll(async () => {
      await context?.close();
    });

    for (const route of ADMIN_ROUTES) {
      test(`keeps a non-admin user out of ${route}`, async () => {
        await page.goto(route);

        await expect(page).toHaveURL(/\/profile$/);
        await expect(
          page.getByRole('heading', { name: 'Profile Settings' }),
        ).toBeVisible();
      });
    }
  });
});
