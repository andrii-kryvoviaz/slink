import type { BrowserContext, Page } from '@playwright/test';

import { expect, test } from '../fixtures/auth.fixture';
import { unique } from '../helpers/accounts';
import type { ApiClient } from '../helpers/api';
import { provisionUser } from '../helpers/provisioning';
import { signInContext } from '../helpers/session';
import { LayoutControls } from '../pages/LayoutControls';
import { PreferencesPage } from '../pages/PreferencesPage';

const BOGUS_THEMES = ['solarized', '"><script>alert(1)</script>'];

test.describe('Theme preference', () => {
  let context: BrowserContext;
  let page: Page;
  let api: ApiClient;
  let preferencesPage: PreferencesPage;
  let layoutControls: LayoutControls;

  test.beforeAll(async ({ browser }) => {
    const account = unique('theme');
    api = await provisionUser(account);

    context = await signInContext(browser, account);
    page = await context.newPage();
    preferencesPage = new PreferencesPage(page);
    layoutControls = new LayoutControls(page);
  });

  test.afterAll(async () => {
    await context?.close();
  });

  test.beforeEach(async () => {
    await api.preferences.updatePreferences({ 'display.theme': 'default' });
  });

  test.afterEach(async () => {
    await api.preferences.updatePreferences({ 'display.theme': 'default' });
  });

  test('picking a theme repaints the surface and persists across reload', async () => {
    await preferencesPage.goto();
    await expect(preferencesPage.heading).toBeVisible();

    expect(await layoutControls.readTheme()).toBe('default');
    const backgroundBefore = await layoutControls.readSurfaceBackground();

    await preferencesPage.selectTheme('nord');
    await preferencesPage.save();

    await expect.poll(() => layoutControls.readTheme()).toBe('nord');
    await expect
      .poll(() => layoutControls.readSettingCookie('theme'))
      .toBe('nord');
    await expect
      .poll(() => layoutControls.readSurfaceBackground())
      .not.toBe(backgroundBefore);

    await page.reload();
    await expect(preferencesPage.heading).toBeVisible();

    expect(await layoutControls.readTheme()).toBe('nord');
    expect(await layoutControls.readSettingCookie('theme')).toBe('nord');
    expect(await layoutControls.readSurfaceBackground()).not.toBe(
      backgroundBefore,
    );
  });

  test('picking a theme repaints the surface in dark mode', async () => {
    await preferencesPage.goto();
    await expect(preferencesPage.heading).toBeVisible();

    await layoutControls.setMode('dark');

    expect(await layoutControls.readColorScheme()).toBe('dark');
    expect(await layoutControls.readTheme()).toBe('default');
    const backgroundBefore = await layoutControls.readSurfaceBackground();

    await preferencesPage.selectTheme('nord');
    await preferencesPage.save();

    await expect.poll(() => layoutControls.readTheme()).toBe('nord');
    await expect
      .poll(() => layoutControls.readSurfaceBackground())
      .not.toBe(backgroundBefore);

    expect(await layoutControls.isDark()).toBe(true);
  });

  test('an unrecognised theme cookie falls back to the default theme', async () => {
    for (const bogusTheme of BOGUS_THEMES) {
      await layoutControls.setThemeCookie(bogusTheme);
      expect(await layoutControls.readSettingCookie('theme')).toBe(bogusTheme);

      const response = await page.goto('/preferences');
      const html = (await response?.text()) ?? '';

      expect(html).toContain('data-theme="default"');
      expect(html).not.toContain(bogusTheme);
      expect(await layoutControls.readTheme()).toBe('default');
    }
  });
});
