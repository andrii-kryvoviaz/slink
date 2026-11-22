import type { Browser, BrowserContext } from '@playwright/test';

import { expect, test } from '../fixtures/auth.fixture';
import { unique } from '../helpers/accounts';
import { provisionUser } from '../helpers/provisioning';
import { signInContext } from '../helpers/session';
import { LayoutControls } from '../pages/LayoutControls';
import { PreferencesPage } from '../pages/PreferencesPage';
import { DEFAULT_CUSTOMIZATION, buildTheme } from '../pages/ThemeSettingsPage';

test.describe('Theme preference saving', { tag: '@serial' }, () => {
  let context: BrowserContext | undefined;

  test.afterEach(async ({ api }) => {
    await context?.close();
    context = undefined;
    await api.settings.updateSettings('customization', DEFAULT_CUSTOMIZATION);
  });

  async function freshUser(browser: Browser) {
    const account = unique('themepref');
    const userApi = await provisionUser(account);

    context = await signInContext(browser, account);
    const page = await context.newPage();

    return { userApi, page };
  }

  test('a user who never picked a theme follows the instance default', async ({
    browser,
    api,
  }) => {
    const { page } = await freshUser(browser);
    const preferences = new PreferencesPage(page);
    const layout = new LayoutControls(page);

    await preferences.goto();
    await preferences.selectOption(preferences.landingPageTrigger, 'Upload');
    await preferences.save();

    await api.settings.updateSettings('customization', {
      ...DEFAULT_CUSTOMIZATION,
      defaultTheme: 'nord',
    });

    await page.reload();
    await expect(preferences.heading).toBeVisible();
    await expect.poll(() => layout.readTheme()).toBe('nord');
  });

  test('a numeric theme id survives the cookie and server render', async ({
    browser,
    api,
  }) => {
    await api.settings.updateSettings('customization', {
      ...DEFAULT_CUSTOMIZATION,
      themes: [buildTheme('2024')],
    });

    const { userApi, page } = await freshUser(browser);
    await userApi.preferences.updatePreferences({ 'display.theme': '2024' });

    const preferences = new PreferencesPage(page);
    const layout = new LayoutControls(page);

    const response = await page.goto(PreferencesPage.URL);
    const html = (await response?.text()) ?? '';
    expect(html).toContain('data-theme="2024"');

    await expect(preferences.heading).toBeVisible();
    await expect(preferences.themeTrigger).toHaveText('2024');
    expect(await layout.readTheme()).toBe('2024');

    await page.reload();
    await expect(preferences.heading).toBeVisible();
    expect(await layout.readTheme()).toBe('2024');
    expect(await layout.readSettingCookie('theme')).toBe('2024');
  });

  test('saving an unrelated preference keeps a theme that is currently hidden', async ({
    browser,
    api,
  }) => {
    const { userApi, page } = await freshUser(browser);
    await userApi.preferences.updatePreferences({ 'display.theme': 'nord' });

    await api.settings.updateSettings('customization', {
      ...DEFAULT_CUSTOMIZATION,
      hiddenThemes: ['nord'],
    });

    const preferences = new PreferencesPage(page);
    const layout = new LayoutControls(page);

    await preferences.goto();
    await expect(preferences.themeTrigger).toHaveText('Default');
    await preferences.selectOption(preferences.landingPageTrigger, 'Upload');
    await preferences.save();

    await api.settings.updateSettings('customization', DEFAULT_CUSTOMIZATION);

    await page.reload();
    await expect(preferences.heading).toBeVisible();
    await expect.poll(() => layout.readTheme()).toBe('nord');
  });
});
