import type { BrowserContext, Page } from '@playwright/test';

import { expect, test } from '../fixtures/auth.fixture';
import { Locale } from '../helpers/Locale';
import { unique } from '../helpers/accounts';
import { provisionUser } from '../helpers/provisioning';
import { signInContext } from '../helpers/session';
import { PreferencesPage } from '../pages/PreferencesPage';

test.describe('Locale preference', { tag: '@serial' }, () => {
  let context: BrowserContext;
  let page: Page;
  let preferencesPage: PreferencesPage;
  let localeHelper: Locale;

  test.beforeAll(async ({ browser }) => {
    const account = unique('locale');
    const api = await provisionUser(account);

    context = await signInContext(browser, account);
    page = await context.newPage();
    preferencesPage = new PreferencesPage(page);
    localeHelper = new Locale(page, api);
  });

  test.afterAll(async () => {
    await context?.close();
  });

  test.beforeEach(async () => {
    await localeHelper.reset();
  });

  test.afterEach(async () => {
    await localeHelper.reset();
  });

  test('changing the display language translates the UI and persists', async () => {
    await preferencesPage.goto();
    await expect(preferencesPage.heading).toBeVisible();

    await preferencesPage.selectLocale('de');
    await preferencesPage.save();

    await expect(
      page.getByRole('heading', { name: 'Einstellungen' }),
    ).toBeVisible();

    await expect.poll(() => localeHelper.read()).toBe('de');

    await page.reload();
    await expect(
      page.getByRole('heading', { name: 'Einstellungen' }),
    ).toBeVisible();
  });
});
