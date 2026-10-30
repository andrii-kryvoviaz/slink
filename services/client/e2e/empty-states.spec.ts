import type { BrowserContext } from '@playwright/test';

import { expect, test } from './fixtures/auth.fixture';
import { unique } from './helpers/accounts';
import { provisionUser } from './helpers/provisioning';
import { signInContext } from './helpers/session';

test.describe('First-run empty states', () => {
  const cases = [
    { path: '/history', title: 'No uploads yet' },
    { path: '/collections', title: 'No collections yet' },
    { path: '/bookmarks', title: 'No bookmarks yet' },
    { path: '/shares', title: 'No shares yet' },
  ];

  for (const { path, title } of cases) {
    test(`shows "${title}" for a fresh user at ${path}`, async ({
      browser,
    }) => {
      const user = unique('empty');
      await provisionUser(user);
      let context: BrowserContext | undefined;

      try {
        context = await signInContext(browser, user);
        const page = await context.newPage();
        await page.goto(path);

        await expect(page.getByRole('heading', { name: title })).toBeVisible();
      } finally {
        await context?.close();
      }
    });
  }
});
