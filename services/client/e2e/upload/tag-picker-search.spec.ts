import type { BrowserContext } from '@playwright/test';

import { expect, test } from '../fixtures/auth.fixture';
import { unique } from '../helpers/accounts';
import { provisionUser } from '../helpers/provisioning';
import { signInContext } from '../helpers/session';
import { UploadPage } from '../pages/UploadPage';

const TARGET_TAG = 'tv';

test.describe('Upload tags option search', { tag: '@sc-1000' }, () => {
  let context: BrowserContext | undefined;

  test.afterEach(async () => {
    await context?.close();
    context = undefined;
  });

  test('does not offer to create a tag that is already listed', async ({
    browser,
  }) => {
    const account = unique('uptagexist');
    const api = await provisionUser(account);
    await api.content.createTag(TARGET_TAG);

    context = await signInContext(browser, account);
    const page = await context.newPage();
    const uploadPage = new UploadPage(page);
    await uploadPage.goto();
    await expect(uploadPage.heading).toBeVisible();

    await uploadPage.searchTags(TARGET_TAG);

    await expect(uploadPage.tagsPickerItem(TARGET_TAG)).toBeVisible();
    await expect(uploadPage.createRow(TARGET_TAG)).toHaveCount(0);
  });
});
