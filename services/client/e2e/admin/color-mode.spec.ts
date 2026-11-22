import type { Browser, Page } from '@playwright/test';

import { expect, test } from '../fixtures/auth.fixture';
import { LayoutControls } from '../pages/LayoutControls';
import { PreferencesPage } from '../pages/PreferencesPage';
import {
  DEFAULT_CUSTOMIZATION,
  ThemeSettingsPage,
  buildTheme,
} from '../pages/ThemeSettingsPage';

const OCEAN_LIGHT = 'rgb(255, 241, 224)';
const OCEAN_DARK = 'rgb(26, 15, 5)';

async function asGuest<T>(
  browser: Browser,
  run: (page: Page) => Promise<T>,
  colorScheme?: 'light' | 'dark',
): Promise<T> {
  const context = await browser.newContext({
    storageState: undefined,
    colorScheme,
  });

  try {
    return await run(await context.newPage());
  } finally {
    await context.close();
  }
}

const htmlClass = (html: string) => /<html[^>]*class="([^"]*)"/.exec(html)?.[1];

const modeSwitch = (page: Page) => page.locator('[data-slot="mode-switch"]');

const withColorMode = (id: string, colorMode: string) => ({
  ...buildTheme(id),
  colorMode,
});

test.describe('Color mode', { tag: '@serial' }, () => {
  let themes: ThemeSettingsPage;

  test.beforeEach(async ({ page, api }) => {
    themes = new ThemeSettingsPage(page);
    await page.emulateMedia({ colorScheme: 'light' });
    await api.settings.updateSettings('customization', DEFAULT_CUSTOMIZATION);
  });

  test.afterEach(async ({ api }) => {
    await api.settings.updateSettings('customization', DEFAULT_CUSTOMIZATION);
    await api.preferences.updatePreferences({ 'display.theme': 'default' });
  });

  test('a dark-only site forces dark for guests and for users who chose light', async ({
    page,
    api,
    browser,
  }) => {
    await api.settings.updateSettings('customization', {
      ...DEFAULT_CUSTOMIZATION,
      colorMode: 'dark',
      defaultTheme: 'ocean',
      themes: [buildTheme('ocean')],
    });
    await api.preferences.updatePreferences({ 'display.theme': 'ocean' });

    await asGuest(browser, async (guest) => {
      const response = await guest.goto('/profile/login');
      expect(htmlClass((await response?.text()) ?? '')).toBe('dark');

      const layout = new LayoutControls(guest);
      await expect.poll(() => layout.readModeClass()).toBe('dark');
      await expect(modeSwitch(guest)).toHaveCount(0);
      const guestTokens = new ThemeSettingsPage(guest);
      expect(await guestTokens.readToken('--color-background')).toBe(
        OCEAN_DARK,
      );
    });

    const layout = new LayoutControls(page);
    await layout.setSettingCookie('mode', 'light');
    const cookie = await layout.readSettingCookie('mode');

    const response = await page.goto('/preferences');
    expect(htmlClass((await response?.text()) ?? '')).toBe('dark');

    await expect.poll(() => layout.readModeClass()).toBe('dark');
    await expect(modeSwitch(page)).toHaveCount(0);
    expect(await themes.readToken('--color-background')).toBe(OCEAN_DARK);
    expect(await layout.readSettingCookie('mode')).toBe(cookie);
  });

  test('allowing both modes again brings back the switch and the light mode', async ({
    page,
    api,
  }) => {
    await api.settings.updateSettings('customization', {
      ...DEFAULT_CUSTOMIZATION,
      colorMode: 'dark',
    });

    const layout = new LayoutControls(page);
    await layout.setSettingCookie('mode', 'light');
    await page.goto('/preferences');
    await expect(modeSwitch(page)).toHaveCount(0);

    await api.settings.updateSettings('customization', DEFAULT_CUSTOMIZATION);
    await page.goto('/preferences');

    await expect(modeSwitch(page)).toHaveCount(1);
    await expect.poll(() => layout.readModeClass()).toBe('light');
    expect(await layout.readSettingCookie('mode')).toBe('light');
  });

  test('a light-only site ignores the system preference', async ({
    page,
    api,
    browser,
  }) => {
    await page.emulateMedia({ colorScheme: 'dark' });
    await api.settings.updateSettings('customization', {
      ...DEFAULT_CUSTOMIZATION,
      colorMode: 'light',
      defaultTheme: 'ocean',
      themes: [buildTheme('ocean')],
    });
    await api.preferences.updatePreferences({ 'display.theme': 'ocean' });

    await asGuest(
      browser,
      async (guest) => {
        const response = await guest.goto('/profile/login');
        expect(htmlClass((await response?.text()) ?? '')).toBe('light');

        const layout = new LayoutControls(guest);
        await expect.poll(() => layout.readModeClass()).toBe('light');
        await expect(modeSwitch(guest)).toHaveCount(0);
      },
      'dark',
    );

    const layout = new LayoutControls(page);
    await layout.setSettingCookie('mode', 'system');
    const cookie = await layout.readSettingCookie('mode');

    await page.goto('/preferences');
    await expect.poll(() => layout.readModeClass()).toBe('light');
    await expect(modeSwitch(page)).toHaveCount(0);
    expect(await layout.readColorScheme()).toBe('light');
    expect(await themes.readToken('--color-background')).toBe(OCEAN_LIGHT);
    expect(await layout.readSettingCookie('mode')).toBe(cookie);
  });

  test('the admin select sets the site mode and saves it', async ({ api }) => {
    await themes.goto();
    await new PreferencesPage(themes.page).selectOption(
      themes.colorModeTrigger,
      'Dark only',
    );
    await themes.save();

    await expect(themes.colorModeTrigger).toHaveText('Dark only');
    const { customization } = await api.settings.getSettings();
    expect(customization.colorMode).toBe('dark');
  });

  test('a dark-only theme forces only the users on that theme', async ({
    page,
    api,
    browser,
  }) => {
    await api.settings.updateSettings('customization', {
      ...DEFAULT_CUSTOMIZATION,
      themes: [withColorMode('ocean', 'dark')],
    });
    await api.preferences.updatePreferences({ 'display.theme': 'ocean' });

    const layout = new LayoutControls(page);
    await layout.setSettingCookie('mode', 'light');
    await page.goto('/preferences');
    await expect.poll(() => layout.readModeClass()).toBe('dark');
    await expect(modeSwitch(page)).toHaveCount(0);
    expect(await themes.readToken('--color-background')).toBe(OCEAN_DARK);

    await asGuest(browser, async (guest) => {
      await guest.goto('/profile/login');
      await expect(modeSwitch(guest)).toHaveCount(1);
      await expect
        .poll(() => new LayoutControls(guest).readModeClass())
        .toBe('system');
    });

    await api.preferences.updatePreferences({ 'display.theme': 'default' });
    await page.goto('/preferences');
    await expect(modeSwitch(page)).toHaveCount(1);
    await expect.poll(() => layout.readModeClass()).toBe('light');
  });

  test('the site setting wins over the theme', async ({ page, api }) => {
    await api.settings.updateSettings('customization', {
      ...DEFAULT_CUSTOMIZATION,
      colorMode: 'light',
      themes: [withColorMode('ocean', 'dark')],
    });
    await api.preferences.updatePreferences({ 'display.theme': 'ocean' });

    const layout = new LayoutControls(page);
    await layout.setSettingCookie('mode', 'dark');
    await page.goto('/preferences');

    await expect.poll(() => layout.readModeClass()).toBe('light');
    await expect(modeSwitch(page)).toHaveCount(0);
    expect(await layout.readTheme()).toBe('ocean');
  });

  test('switching theme in preferences swaps the mode without a reload', async ({
    page,
    api,
  }) => {
    await api.settings.updateSettings('customization', {
      ...DEFAULT_CUSTOMIZATION,
      themes: [withColorMode('ocean', 'dark')],
    });

    const layout = new LayoutControls(page);
    await layout.setSettingCookie('mode', 'light');

    const preferences = new PreferencesPage(page);
    await preferences.goto();
    await expect(modeSwitch(page)).toHaveCount(1);
    await expect.poll(() => layout.readModeClass()).toBe('light');

    await preferences.selectTheme('Ocean');
    await preferences.save();
    await expect(modeSwitch(page)).toHaveCount(0);
    await expect.poll(() => layout.readModeClass()).toBe('dark');

    await preferences.selectTheme('Default');
    await preferences.save();
    await expect(modeSwitch(page)).toHaveCount(1);
    await expect.poll(() => layout.readModeClass()).toBe('light');
  });

  test('the editor hides the other mode and keeps its values', async ({
    page,
    api,
  }) => {
    await themes.goto();
    await themes.openEditor(themes.addButton);
    await themes.nameInput.fill('Dusk');
    await themes.color('dark', 'background').fill('nope');

    const preferences = new PreferencesPage(page);
    await preferences.selectOption(themes.editorColorModeTrigger, 'Light only');
    await expect(themes.color('dark', 'background')).toBeVisible();
    await expect(themes.submitButton).toBeDisabled();

    await themes.color('dark', 'background').fill('#123456');
    await expect(themes.color('dark', 'background')).toHaveCount(0);
    await expect(themes.color('light', 'background')).toBeVisible();
    await expect(themes.submitButton).toBeEnabled();

    await themes.applyEditor();
    await expect(themes.row('dusk')).toContainText('Light only');
    await themes.save();

    const { customization } = await api.settings.getSettings();
    expect(customization.themes).toEqual([
      expect.objectContaining({
        id: 'dusk',
        colorMode: 'light',
        dark: expect.objectContaining({ background: '#123456' }),
      }),
    ]);

    await themes.openEditor(themes.action('dusk', 'Edit'));
    await expect(themes.color('dark', 'background')).toHaveCount(0);
    await preferences.selectOption(
      themes.editorColorModeTrigger,
      'Light and dark',
    );
    await expect(themes.color('dark', 'background')).toHaveValue('#123456');
    await themes.editor.getByRole('button', { name: 'Cancel' }).click();
    await expect(themes.editor).toHaveCount(0);
  });

  test('a built-in theme with only a color mode keeps its id and stock colors', async ({
    page,
    api,
  }) => {
    await api.preferences.updatePreferences({ 'display.theme': 'nord' });

    await themes.goto();
    const preferences = new PreferencesPage(page);
    await themes.openEditor(themes.action('nord', 'Edit'));
    await preferences.selectOption(themes.editorColorModeTrigger, 'Dark only');
    await themes.applyEditor();
    await expect(themes.row('nord')).toContainText('Edited');
    await themes.save();

    const { customization } = await api.settings.getSettings();
    expect(customization.themes).toEqual([
      {
        id: 'nord',
        name: 'Nord',
        light: null,
        dark: null,
        radius: null,
        colorMode: 'dark',
        css: '',
      },
    ]);

    const layout = new LayoutControls(page);
    expect(await layout.readTheme()).toBe('nord');
    await expect.poll(() => layout.readModeClass()).toBe('dark');

    await themes.openEditor(themes.action('nord', 'Edit'));
    await preferences.selectOption(
      themes.editorColorModeTrigger,
      'Light and dark',
    );
    await themes.applyEditor();
    await themes.save();

    const reverted = await api.settings.getSettings();
    expect(reverted.customization.themes).toEqual([]);
  });

  test('rejects an invalid color mode for the site and for a theme', async ({
    api,
  }) => {
    const siteError = await api.settings
      .updateSettings('customization', {
        ...DEFAULT_CUSTOMIZATION,
        colorMode: 'sepia',
      })
      .catch((error: Error) => error);

    expect(JSON.stringify(siteError) + String(siteError)).toContain(
      'customization.colorMode',
    );

    const themeError = await api.settings
      .updateSettings('customization', {
        ...DEFAULT_CUSTOMIZATION,
        themes: [withColorMode('ocean', 'sepia')],
      })
      .catch((error: Error) => error);

    expect(JSON.stringify(themeError) + String(themeError)).toContain(
      'customization.themes',
    );

    const { customization } = await api.settings.getSettings();
    expect(customization.colorMode).toBe('both');
    expect(customization.themes).toEqual([]);
  });
});
