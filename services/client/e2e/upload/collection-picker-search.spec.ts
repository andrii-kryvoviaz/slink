import type { BrowserContext, Request } from '@playwright/test';

import { expect, test } from '../fixtures/auth.fixture';
import { unique } from '../helpers/accounts';
import { seedCollectionsAhead } from '../helpers/collections';
import { provisionUser } from '../helpers/provisioning';
import { signInContext } from '../helpers/session';
import { UploadPage } from '../pages/UploadPage';

const TARGET_COLLECTION = 'Harbor Archive';

const isCollectionSearchFor = (term: string) => (request: Request) => {
  const url = new URL(request.url());

  return (
    request.method() === 'GET' &&
    url.pathname === '/api/collections' &&
    url.searchParams.get('searchTerm') === term
  );
};

test.describe('Upload collections option search', { tag: '@sc-1000' }, () => {
  let context: BrowserContext | undefined;

  test.afterEach(async () => {
    await context?.close();
    context = undefined;
  });

  test('finds a collection past the first fifty by searching on the server', async ({
    browser,
  }) => {
    const account = unique('upcolsrch');
    const api = await provisionUser(account);
    await seedCollectionsAhead(api, TARGET_COLLECTION);

    context = await signInContext(browser, account);
    const page = await context.newPage();
    const uploadPage = new UploadPage(page);
    await uploadPage.goto();
    await expect(uploadPage.heading).toBeVisible();

    const searched = page.waitForRequest(
      isCollectionSearchFor(TARGET_COLLECTION),
      { timeout: 15000 },
    );
    await uploadPage.searchCollections(TARGET_COLLECTION);
    await searched;

    const row = uploadPage.collectionsPickerItem(TARGET_COLLECTION);
    await expect(row).toBeVisible();
    await expect(row).toHaveAttribute('aria-pressed', 'false');
  });
});
