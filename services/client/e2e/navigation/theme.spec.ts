import { expect, test } from '../fixtures/auth.fixture';

test.describe('Theme', () => {
  test('toggles theme, applies the dark class, and persists across reload', async ({
    page,
    layoutControls,
  }) => {
    await page.goto('/');
    await layoutControls.themeToggle.waitFor({ state: 'visible' });

    const before = await layoutControls.isDark();
    await layoutControls.toggleTheme();
    await expect.poll(() => layoutControls.isDark()).toBe(!before);

    const expectedTheme = before ? 'light' : 'dark';
    await expect
      .poll(() => layoutControls.readSettingCookie('theme'))
      .toBe(expectedTheme);

    await page.reload();
    await layoutControls.themeToggle.waitFor({ state: 'visible' });

    expect(await layoutControls.isDark()).toBe(!before);
    expect(await layoutControls.readSettingCookie('theme')).toBe(expectedTheme);
  });
});
