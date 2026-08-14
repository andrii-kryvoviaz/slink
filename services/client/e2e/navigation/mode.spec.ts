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

    const before = await layoutControls.isDark();
    expect(await layoutControls.readColorScheme()).toBe(
      before ? 'dark' : 'light',
    );

    await layoutControls.toggleMode();
    await expect.poll(() => layoutControls.isDark()).toBe(!before);

    expect(await layoutControls.readColorScheme()).toBe(
      before ? 'light' : 'dark',
    );
  });
});
