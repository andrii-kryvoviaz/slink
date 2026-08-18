import { expect, test } from '../fixtures/auth.fixture';

test.describe('Mode', () => {
  test('toggles mode, applies the dark class, and persists across reload', async ({
    page,
    layoutControls,
  }) => {
    await page.goto('/');
    await layoutControls.modeToggle.waitFor({ state: 'visible' });

    const before = await layoutControls.isDark();
    await layoutControls.toggleMode();
    await expect.poll(() => layoutControls.isDark()).toBe(!before);

    const expectedMode = before ? 'light' : 'dark';
    await expect
      .poll(() => layoutControls.readSettingCookie('mode'))
      .toBe(expectedMode);

    await page.reload();
    await layoutControls.modeToggle.waitFor({ state: 'visible' });

    expect(await layoutControls.isDark()).toBe(!before);
    expect(await layoutControls.readSettingCookie('mode')).toBe(expectedMode);
  });

  test('the document color scheme follows the mode in both modes', async ({
    page,
    layoutControls,
  }) => {
    await page.goto('/');
    await layoutControls.modeToggle.waitFor({ state: 'visible' });

    await layoutControls.setMode('light');
    await expect.poll(() => layoutControls.readColorScheme()).toBe('light');

    await layoutControls.setMode('dark');
    await expect.poll(() => layoutControls.readColorScheme()).toBe('dark');
  });

  test('defaults to system mode without a cookie and switches to an explicit mode on toggle', async ({
    page,
    layoutControls,
  }) => {
    await page.context().clearCookies({ name: 'settings.mode' });
    await page.goto('/');
    await layoutControls.modeToggle.waitFor({ state: 'visible' });

    expect(await layoutControls.readModeClass()).toBe('system');
    expect(await layoutControls.readColorScheme()).toBe('light dark');

    await layoutControls.toggleMode();
    await expect.poll(() => layoutControls.readModeClass()).toBe('dark');
    await expect
      .poll(() => layoutControls.readSettingCookie('mode'))
      .toBe('dark');
  });
});
