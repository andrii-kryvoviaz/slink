import type { Browser, Page } from '@playwright/test';

import { expect, test } from '../fixtures/auth.fixture';
import { LayoutControls } from '../pages/LayoutControls';
import { PreferencesPage } from '../pages/PreferencesPage';
import {
  DEFAULT_CUSTOMIZATION,
  ThemeSettingsPage,
  buildTheme,
} from '../pages/ThemeSettingsPage';

async function asGuest<T>(
  browser: Browser,
  run: (page: Page) => Promise<T>,
): Promise<T> {
  const context = await browser.newContext({ storageState: undefined });

  try {
    return await run(await context.newPage());
  } finally {
    await context.close();
  }
}

const guestTheme = (browser: Browser) =>
  asGuest(browser, async (page) => {
    const response = await page.goto('/profile/login');
    const html = (await response?.text()) ?? '';

    return /<html[^>]*data-theme="([^"]*)"/.exec(html)?.[1];
  });

const guestHtml = (browser: Browser) =>
  asGuest(browser, async (page) => {
    const response = await page.goto('/profile/login');

    return (await response?.text()) ?? '';
  });

test.describe('Admin theme settings', { tag: '@serial' }, () => {
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

  test('adds a theme that users can select', async ({ page, api }) => {
    await themes.goto();
    await themes.openEditor(themes.addButton);

    await expect(themes.submitButton).toBeDisabled();
    await themes.nameInput.fill('Öcean Breeze');
    await themes.color('light', 'background').fill('#ddeeff');
    await themes.radiusInput.fill('0');
    await themes.applyEditor();

    await expect(themes.row('ocean-breeze')).toBeVisible();
    await expect(themes.dirtyHint).toBeVisible();

    await themes.save();
    await expect(themes.row('ocean-breeze')).toContainText('Öcean Breeze');
    await expect(themes.dirtyHint).toHaveCount(0);

    const preferences = new PreferencesPage(page);
    await preferences.goto();
    await preferences.selectTheme('Öcean Breeze');
    await preferences.save();

    const layout = new LayoutControls(page);
    await expect.poll(() => layout.readTheme()).toBe('ocean-breeze');

    const response = await page.reload();
    const html = (await response?.text()) ?? '';
    expect(html).toContain('data-theme="ocean-breeze"');
    expect(html).toContain('id="slink-themes"');

    await layout.setMode('light');
    expect(await themes.readToken('--color-background')).toBe(
      'rgb(221, 238, 255)',
    );
    expect(await themes.readToken('--radius')).toBe('0rem');

    const { customization } = await api.settings.getSettings();
    expect(customization.themes[0].id).toBe('ocean-breeze');
  });

  test('starts a new theme from the default colors in both modes', async () => {
    await themes.goto();
    await themes.openEditor(themes.addButton);

    await expect(themes.color('light', 'background')).toHaveValue('#ffffff');
    await expect(themes.color('dark', 'background')).toHaveValue('#09090b');
    expect(await themes.color('light', 'success').inputValue()).not.toBe(
      await themes.color('dark', 'success').inputValue(),
    );
  });

  test('keeps the picker and the hex field in sync', async () => {
    await themes.goto();
    await themes.openEditor(themes.addButton);

    const picker = themes.editor.locator('input[name="light-accent-picker"]');
    await picker.fill('#123456');
    await expect(themes.color('light', 'accent')).toHaveValue('#123456');

    await themes.color('light', 'accent').fill('#abcdef');
    await expect(picker).toHaveValue('#abcdef');
  });

  test('blocks invalid colors and radius in the editor', async () => {
    await themes.goto();
    await themes.openEditor(themes.addButton);
    await themes.nameInput.fill('Broken');
    await expect(themes.submitButton).toBeEnabled();

    await themes.color('dark', 'danger').fill('red');
    await expect(themes.submitButton).toBeDisabled();

    await themes.color('dark', 'danger').fill('#ff0000');
    await expect(themes.submitButton).toBeEnabled();

    await themes.radiusInput.fill('9');
    await expect(themes.submitButton).toBeDisabled();

    await themes.radiusInput.fill('abc');
    await expect(themes.submitButton).toBeDisabled();
  });

  test('discards a cancelled edit', async () => {
    await themes.goto();
    await themes.openEditor(themes.addButton);
    await themes.nameInput.fill('Abandoned');
    await themes.editor.getByRole('button', { name: 'Cancel' }).click();

    await expect(themes.editor).toHaveCount(0);
    await expect(themes.row('abandoned')).toHaveCount(0);
    await expect(themes.dirtyHint).toHaveCount(0);
  });

  test('edits and reverts a built-in theme', async ({ page, api }) => {
    await api.preferences.updatePreferences({ 'display.theme': 'nord' });

    await themes.goto();
    await themes.openEditor(themes.action('nord', 'Edit'));
    await expect(themes.nameInput).toHaveValue('Nord');
    await themes.nameInput.fill('Fjord');
    await themes.color('light', 'background').fill('#c0ffee');
    await themes.applyEditor();
    await expect(themes.row('nord')).toContainText('Edited');
    await themes.save();

    const layout = new LayoutControls(page);
    expect(await layout.readTheme()).toBe('edited:nord');
    await layout.setMode('light');
    expect(await themes.readToken('--color-background')).toBe(
      'rgb(192, 255, 238)',
    );

    await themes.clickUntilGone(themes.action('nord', 'Revert'));
    await themes.save();

    expect(await layout.readTheme()).toBe('nord');
    await expect(themes.row('nord')).toContainText('Nord');
    const { customization } = await api.settings.getSettings();
    expect(customization.themes).toEqual([]);
  });

  test('an edited default theme drops the stock preset', async ({
    page,
    api,
  }) => {
    await api.settings.updateSettings('customization', {
      ...DEFAULT_CUSTOMIZATION,
      themes: [buildTheme('default')],
    });

    await page.goto('/preferences');
    const layout = new LayoutControls(page);
    expect(await layout.readTheme()).toBe('edited:default');
    await layout.setMode('light');
    expect(await themes.readToken('--color-background')).toBe(
      'rgb(255, 241, 224)',
    );
  });

  test('edits a custom theme without changing its id', async ({ api }) => {
    await api.settings.updateSettings('customization', {
      ...DEFAULT_CUSTOMIZATION,
      themes: [buildTheme('ocean')],
    });

    await themes.goto();
    await themes.openEditor(themes.action('ocean', 'Edit'));
    await expect(themes.color('light', 'background')).toHaveValue('#fff1e0');
    await themes.nameInput.fill('Deep Sea');
    await themes.applyEditor();
    await themes.save();

    const { customization } = await api.settings.getSettings();
    expect(customization.themes).toEqual([
      { ...buildTheme('ocean'), name: 'Deep Sea' },
    ]);
  });

  test('duplicates a theme under a new id', async ({ api }) => {
    await themes.goto();
    await themes.openEditor(themes.action('gruvbox', 'Duplicate'));
    await themes.nameInput.fill('Gruvbox');
    await themes.applyEditor();
    await expect(themes.row('gruvbox-2')).toBeVisible();
    await themes.save();

    const { customization } = await api.settings.getSettings();
    expect(customization.themes.map(({ id }: { id: string }) => id)).toEqual([
      'gruvbox-2',
    ]);
  });

  test('deletes a custom theme and moves its users to the default', async ({
    page,
    api,
  }) => {
    await api.settings.updateSettings('customization', {
      ...DEFAULT_CUSTOMIZATION,
      defaultTheme: 'ocean',
      themes: [buildTheme('ocean')],
    });
    await api.preferences.updatePreferences({ 'display.theme': 'ocean' });

    await themes.goto();
    const layout = new LayoutControls(page);
    expect(await layout.readTheme()).toBe('ocean');

    await themes.clickUntilGone(themes.row('ocean').getByLabel(/^Delete/));
    await themes.save();

    expect(await layout.readTheme()).toBe('default');
    const { customization } = await api.settings.getSettings();
    expect(customization.themes).toEqual([]);
    expect(customization.defaultTheme).toBe('default');
  });

  test('hides and restores a built-in theme', async ({ page, api }) => {
    await api.preferences.updatePreferences({ 'display.theme': 'nord' });

    await themes.goto();
    await themes.clickUntilGone(themes.action('nord', 'Hide'));
    await themes.save();

    await expect(themes.row('nord')).toContainText('Hidden');
    expect(await new LayoutControls(page).readTheme()).toBe('default');

    const preferences = new PreferencesPage(page);
    await preferences.goto();
    await preferences.clickUntil(
      preferences.themeTrigger,
      page.getByRole('option', { name: 'Gruvbox', exact: true }),
    );
    await expect(
      page.getByRole('option', { name: 'Nord', exact: true }),
    ).toHaveCount(0);

    await themes.goto();
    await themes.clickUntilGone(themes.action('nord', 'Restore'));
    await themes.save();

    await expect(themes.row('nord')).not.toContainText('Hidden');
    expect(await new LayoutControls(page).readTheme()).toBe('nord');
  });

  test('turning the selector off forces the default theme', async ({
    page,
    api,
    browser,
  }) => {
    await api.preferences.updatePreferences({ 'display.theme': 'nord' });
    await api.settings.updateSettings('customization', {
      ...DEFAULT_CUSTOMIZATION,
      defaultTheme: 'gruvbox',
    });

    await themes.goto();
    expect(await new LayoutControls(page).readTheme()).toBe('nord');
    expect(await guestTheme(browser)).toBe('gruvbox');

    await expect(async () => {
      await themes.selectorSwitch.click();
      await expect(themes.selectorSwitch).toHaveAttribute(
        'aria-checked',
        'false',
        { timeout: 1000 },
      );
    }).toPass({ timeout: 15000 });
    await themes.save();

    expect(await new LayoutControls(page).readTheme()).toBe('gruvbox');

    const preferences = new PreferencesPage(page);
    await preferences.goto();
    await expect(preferences.navigationSection).toBeVisible();
    await expect(preferences.appearanceSection).toHaveCount(0);
    await preferences.save();
    expect(await new LayoutControls(page).readTheme()).toBe('gruvbox');

    await api.settings.updateSettings('customization', DEFAULT_CUSTOMIZATION);
    await page.reload();
    expect(await new LayoutControls(page).readTheme()).toBe('nord');
  });

  test('the default theme select drives guests', async ({ page, browser }) => {
    await themes.goto();
    const trigger = page.locator(
      'xpath=//h3[normalize-space()="Default Theme"]/ancestor::div[.//*[@data-slot="select-trigger"]][1]//*[@data-slot="select-trigger"]',
    );
    await new PreferencesPage(page).selectOption(trigger, 'Catppuccin');
    await themes.save();

    expect(await guestTheme(browser)).toBe('catppuccin');
  });

  test('rejects an unsafe theme with a customization.themes violation', async ({
    api,
  }) => {
    const unsafe = buildTheme('ocean', '#fff}</style><script>');

    const response = await api.settings
      .updateSettings('customization', {
        ...DEFAULT_CUSTOMIZATION,
        themes: [unsafe],
      })
      .catch((error: Error) => error);

    expect(JSON.stringify(response) + String(response)).toContain(
      'customization.themes',
    );
    const { customization } = await api.settings.getSettings();
    expect(customization.themes).toEqual([]);
  });
});

test.describe('Theme custom CSS', { tag: '@serial' }, () => {
  let themes: ThemeSettingsPage;

  test.beforeEach(({ page }) => {
    themes = new ThemeSettingsPage(page);
  });

  test.afterEach(async ({ api }) => {
    await api.settings.updateSettings('customization', DEFAULT_CUSTOMIZATION);
    await api.preferences.updatePreferences({ 'display.theme': 'default' });
  });

  test('is saved in the editor, server-rendered for its users and survives a reload', async ({
    page,
    api,
    browser,
  }) => {
    const css = ':root { --radius: 3px; } body { --probe: "a > b & c"; }';

    await themes.goto();
    await themes.openEditor(themes.addButton);
    await themes.nameInput.fill('Ocean');
    await themes.themeCss.fill(css);
    await themes.applyEditor();
    await themes.save();

    const { customization } = await api.settings.getSettings();
    expect(customization.themes[0].css).toBe(css);

    const preferences = new PreferencesPage(page);
    await preferences.goto();
    await preferences.selectTheme('Ocean');
    await preferences.save();

    const response = await page.reload();
    const html = (await response?.text()) ?? '';
    expect(html).toContain('data-theme="ocean"');
    expect(html).toContain(`<style id="slink-theme-css">${css}</style>`);

    expect(await themes.readToken('--radius')).toBe('3px');
    await page.reload();
    expect(await themes.readToken('--radius')).toBe('3px');
    await expect(page.locator('#slink-theme-css')).toHaveJSProperty(
      'textContent',
      css,
    );

    expect(await guestHtml(browser)).not.toContain('slink-theme-css');
  });

  test('is not sent to users on another theme', async ({
    page,
    api,
    browser,
  }) => {
    const css = ':root { --radius: 3px; }';
    await api.settings.updateSettings('customization', {
      ...DEFAULT_CUSTOMIZATION,
      themes: [{ ...buildTheme('ocean'), css }],
    });
    await api.preferences.updatePreferences({ 'display.theme': 'default' });

    await page.goto('/preferences');
    await expect(page.locator('#slink-theme-css')).toHaveCount(0);
    expect(await themes.readToken('--radius')).not.toBe('3px');

    expect(await guestHtml(browser)).not.toContain('slink-theme-css');
    expect(await guestTheme(browser)).toBe('default');
  });

  test('switching theme in preferences swaps the css without a reload', async ({
    page,
    api,
  }) => {
    const css = ':root { --radius: 3px; }';
    await api.settings.updateSettings('customization', {
      ...DEFAULT_CUSTOMIZATION,
      themes: [{ ...buildTheme('ocean'), css }],
    });
    await api.preferences.updatePreferences({ 'display.theme': 'default' });

    const preferences = new PreferencesPage(page);
    await preferences.goto();
    const style = page.locator('#slink-theme-css');
    await expect(style).toHaveCount(0);

    await preferences.selectTheme('Ocean');
    await preferences.save();
    await expect(style).toHaveJSProperty('textContent', css);
    await expect.poll(() => themes.readToken('--radius')).toBe('3px');

    await preferences.selectTheme('Default');
    await preferences.save();
    await expect(style).toHaveCount(0);
    await expect.poll(() => themes.readToken('--radius')).not.toBe('3px');
  });

  test('overrides theme tokens, font and stable slots', async ({
    page,
    api,
  }) => {
    await api.settings.updateSettings('customization', {
      ...DEFAULT_CUSTOMIZATION,
      defaultTheme: 'ocean',
      themes: [
        {
          ...buildTheme('ocean'),
          css: `
            :root { --color-background: rgb(1, 2, 3); --font-sans: monospace; }
            [data-slot='app-header'] { border-bottom-width: 7px; }
          `,
        },
      ],
      themeSelectorEnabled: false,
    });

    await page.goto('/preferences');

    expect(await themes.readToken('--color-background')).toBe('rgb(1, 2, 3)');
    await expect(page.locator('body')).toHaveCSS('font-family', 'monospace');
    await expect(page.locator('[data-slot="app-header"]')).toHaveCSS(
      'border-bottom-width',
      '7px',
    );
  });

  test('cannot break out of the style element', async ({ page, api }) => {
    await api.settings.updateSettings('customization', {
      ...DEFAULT_CUSTOMIZATION,
      defaultTheme: 'ocean',
      themes: [
        {
          ...buildTheme('ocean'),
          css: '</style><script>window.__pwned = true</script>',
        },
      ],
      themeSelectorEnabled: false,
    });

    await page.goto('/preferences');

    expect(
      await page.evaluate(() => (window as { __pwned?: boolean }).__pwned),
    ).toBeUndefined();
    await expect(page.locator('#slink-theme-css')).toHaveCount(1);
  });

  test('can be switched off from the address bar', async ({ page, api }) => {
    await api.settings.updateSettings('customization', {
      ...DEFAULT_CUSTOMIZATION,
      themes: [{ ...buildTheme('ocean'), css: 'body { display: none; }' }],
    });
    await api.preferences.updatePreferences({ 'display.theme': 'ocean' });

    await page.goto(ThemeSettingsPage.URL);
    await expect(themes.list).toBeHidden();

    await themes.goto('?custom-css=off');
    await expect(page.locator('#slink-theme-css')).toHaveCount(0);
  });

  test('adds css to the stock default theme without changing its colors', async ({
    page,
    api,
  }) => {
    const css = ':root { --radius: 3px; }';
    const tokens = ['--color-background', '--color-border', '--color-muted'];
    const layout = new LayoutControls(page);

    await api.preferences.updatePreferences({ 'display.theme': 'default' });
    await themes.goto();
    await layout.setMode('light');
    const before = await Promise.all(tokens.map((t) => themes.readToken(t)));

    await themes.openEditor(themes.action('default', 'Edit'));
    await themes.themeCss.fill(css);
    await themes.applyEditor();
    await themes.save();

    const { customization } = await api.settings.getSettings();
    expect(customization.themes).toEqual([
      {
        id: 'default',
        name: 'Default',
        light: null,
        dark: null,
        radius: null,
        css,
      },
    ]);

    const response = await page.reload();
    expect((await response?.text()) ?? '').toContain('data-theme="default"');
    expect(await layout.readTheme()).toBe('default');
    await layout.setMode('light');
    expect(await themes.readToken('--radius')).toBe('3px');
    expect(await Promise.all(tokens.map((t) => themes.readToken(t)))).toEqual(
      before,
    );

    await themes.clickUntilGone(themes.action('default', 'Revert'));
    await themes.save();

    const reverted = await api.settings.getSettings();
    expect(reverted.customization.themes).toEqual([]);
  });

  test('deleting a theme stops its css', async ({ page, api }) => {
    const css = ':root { --radius: 3px; }';
    await api.settings.updateSettings('customization', {
      ...DEFAULT_CUSTOMIZATION,
      themes: [{ ...buildTheme('ocean'), css }],
    });
    await api.preferences.updatePreferences({ 'display.theme': 'ocean' });

    await themes.goto();
    await expect(page.locator('#slink-theme-css')).toHaveJSProperty(
      'textContent',
      css,
    );

    await themes.clickUntilGone(themes.row('ocean').getByLabel(/^Delete/));
    await themes.save();

    await expect(page.locator('#slink-theme-css')).toHaveCount(0);
    expect(await themes.readToken('--radius')).not.toBe('3px');
  });

  test('turning the selector off stops the css', async ({ page, api }) => {
    const css = ':root { --radius: 3px; }';
    await api.settings.updateSettings('customization', {
      ...DEFAULT_CUSTOMIZATION,
      themes: [{ ...buildTheme('ocean'), css }],
    });
    await api.preferences.updatePreferences({ 'display.theme': 'ocean' });

    await themes.goto();
    await expect(page.locator('#slink-theme-css')).toHaveJSProperty(
      'textContent',
      css,
    );

    await expect(async () => {
      await themes.selectorSwitch.click();
      await expect(themes.selectorSwitch).toHaveAttribute(
        'aria-checked',
        'false',
        { timeout: 1000 },
      );
    }).toPass({ timeout: 15000 });
    await themes.save();

    await expect(page.locator('#slink-theme-css')).toHaveCount(0);
    expect(await new LayoutControls(page).readTheme()).toBe('default');
  });

  test('is copied when a theme is duplicated', async ({ api }) => {
    const css = ':root { --radius: 3px; }';
    await api.settings.updateSettings('customization', {
      ...DEFAULT_CUSTOMIZATION,
      themes: [{ ...buildTheme('ocean'), css }],
    });

    await themes.goto();
    await themes.openEditor(themes.action('ocean', 'Duplicate'));
    await expect(themes.themeCss).toHaveValue(css);
    await themes.applyEditor();
    await expect(themes.row('ocean-2')).toBeVisible();
    await themes.save();

    const { customization } = await api.settings.getSettings();
    expect(
      customization.themes.map(({ id, css }: { id: string; css: string }) => ({
        id,
        css,
      })),
    ).toEqual([
      { id: 'ocean', css },
      { id: 'ocean-2', css },
    ]);
  });

  test('rejects oversized css with a customization.themes violation', async ({
    api,
  }) => {
    const response = await api.settings
      .updateSettings('customization', {
        ...DEFAULT_CUSTOMIZATION,
        themes: [{ ...buildTheme('ocean'), css: 'a'.repeat(65537) }],
      })
      .catch((error: Error) => error);

    expect(JSON.stringify(response) + String(response)).toContain(
      'customization.themes',
    );
    const { customization } = await api.settings.getSettings();
    expect(customization.themes).toEqual([]);
  });

  test('a theme saved without css still renders', async ({ page, api }) => {
    const { css: _css, ...legacy } = buildTheme('ocean');

    await api.settings.updateSettings('customization', {
      ...DEFAULT_CUSTOMIZATION,
      themes: [legacy as ReturnType<typeof buildTheme>],
      defaultTheme: 'ocean',
      themeSelectorEnabled: false,
    });

    await page.goto('/preferences');
    await expect(page.locator('[data-slot="app"]')).toBeVisible();
    await expect(page.locator('#slink-theme-css')).toHaveCount(0);
    expect(await new LayoutControls(page).readTheme()).toBe('ocean');
  });
});
