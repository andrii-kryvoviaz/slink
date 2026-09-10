import type { BrowserContext, Page } from '@playwright/test';

import { expect, test } from '../fixtures/auth.fixture';
import { unique } from '../helpers/accounts';
import { provisionUser } from '../helpers/provisioning';
import { signInContext } from '../helpers/session';
import { LayoutControls } from '../pages/LayoutControls';
import { PreferencesPage } from '../pages/PreferencesPage';

test.describe('Auto-publish preference', { tag: '@serial' }, () => {
  let context: BrowserContext;
  let page: Page;
  let preferencesPage: PreferencesPage;
  let layoutControls: LayoutControls;

  test.beforeAll(async ({ browser }) => {
    const account = unique('apub');
    const api = await provisionUser(account);

    await api.preferences.updatePreferences({
      'image.externalUploadAutoPublish': true,
    });

    context = await signInContext(browser, account);
    page = await context.newPage();
    preferencesPage = new PreferencesPage(page);
    layoutControls = new LayoutControls(page);
  });

  test.afterAll(async () => {
    await context?.close();
  });

  test('stays visible and enabled when only public images are allowed', async ({
    settingsApi,
  }) => {
    await settingsApi.set('image', { allowOnlyPublicImages: true });

    await preferencesPage.goto();
    await expect(preferencesPage.heading).toBeVisible();

    await expect(preferencesPage.autoPublishSwitch).toBeVisible();
    await expect(preferencesPage.autoPublishSwitch).toHaveAttribute(
      'aria-checked',
      'true',
    );

    await preferencesPage.selectTheme('nord');
    await preferencesPage.save();

    await expect.poll(() => layoutControls.readTheme()).toBe('nord');

    await page.reload();
    await expect(preferencesPage.heading).toBeVisible();

    await expect(preferencesPage.autoPublishSwitch).toHaveAttribute(
      'aria-checked',
      'true',
    );
  });
});
