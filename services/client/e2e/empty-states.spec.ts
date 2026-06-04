import type { BrowserContext } from '@playwright/test';

import { expect, test } from './fixtures/auth.fixture';
import { provisionUser, signInContext } from './helpers/auth';
import { uniqueUser } from './helpers/testUsers';

test.describe('First-run empty states', () => {
  const cases = [
    { path: '/history', title: 'No history yet' },
    { path: '/collections', title: 'No collections found' },
    { path: '/bookmarks', title: 'No bookmarks yet' },
    { path: '/shares', title: 'No shares yet' },
  ];

  for (const { path, title } of cases) {
    test(`shows "${title}" for a fresh user at ${path}`, async ({
      browser,
    }) => {
      const user = uniqueUser('empty');
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

test.describe('Explore empty state', { tag: '@anonymous' }, () => {
  test('shows the upload CTA when there are no public images', async ({
    page,
  }) => {
    await page.goto('/explore');

    const emptyHeading = page.getByRole('heading', { name: 'No images yet' });
    const cta = page.getByRole('link', { name: 'Upload First Image' });
    const feedItems = page.locator('main [role="button"][tabindex="0"]');

    await expect
      .poll(
        async () => {
          if (await emptyHeading.isVisible()) {
            return 'empty';
          }
          if ((await feedItems.count()) > 0) {
            return 'populated';
          }
          return 'pending';
        },
        { timeout: 15000 },
      )
      .not.toBe('pending');

    if (await emptyHeading.isVisible()) {
      await expect(cta).toBeVisible();
    }
  });
});
