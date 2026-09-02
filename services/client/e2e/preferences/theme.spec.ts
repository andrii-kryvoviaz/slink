import { expect, test } from '../fixtures/auth.fixture';

const BOGUS_THEMES = ['solarized', '"><script>alert(1)</script>'];

test.describe('Theme preference', () => {
  test.beforeEach(async ({ api }) => {
    await api.preferences.updatePreferences({ 'display.theme': 'default' });
  });

  test.afterEach(async ({ api }) => {
    await api.preferences.updatePreferences({ 'display.theme': 'default' });
  });

  test('picking a theme repaints the surface and persists across reload', async ({
    page,
    preferencesPage,
    layoutControls,
  }) => {
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

  test('picking a theme repaints the surface in dark mode', async ({
    preferencesPage,
    layoutControls,
  }) => {
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

  test('an unrecognised theme cookie falls back to the default theme', async ({
    page,
    layoutControls,
  }) => {
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
