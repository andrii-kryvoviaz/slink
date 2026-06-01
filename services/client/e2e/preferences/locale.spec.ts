import { expect, test } from '../fixtures/auth.fixture';
import type { LayoutControls } from '../pages/LayoutControls';
import type { PreferencesPage } from '../pages/PreferencesPage';

test.describe('Locale preference', () => {
  test.use({ storageState: 'e2e/.auth/user.json' });

  const resetToEnglish = async (
    preferencesPage: PreferencesPage,
    layoutControls: LayoutControls,
  ) => {
    const cookie = await layoutControls.readSettingCookie('locale');
    if (cookie && cookie !== 'en') {
      await preferencesPage.goto();
      await preferencesPage.localeTrigger.waitFor({ state: 'visible' });
      await preferencesPage.selectLocale('en');
      await preferencesPage.save();
      await expect
        .poll(() => layoutControls.readSettingCookie('locale'))
        .toBe('en');
    }
  };

  test.beforeEach(async ({ preferencesPage, layoutControls }) => {
    await resetToEnglish(preferencesPage, layoutControls);
  });

  test.afterEach(async ({ preferencesPage, layoutControls }) => {
    await resetToEnglish(preferencesPage, layoutControls);
  });

  test('changing the display language translates the UI and persists', async ({
    page,
    preferencesPage,
    layoutControls,
  }) => {
    await preferencesPage.goto();
    await expect(preferencesPage.heading).toBeVisible();

    await preferencesPage.selectLocale('de');
    await preferencesPage.save();

    await expect(
      page.getByRole('heading', { name: 'Einstellungen' }),
    ).toBeVisible();

    await expect
      .poll(() => layoutControls.readSettingCookie('locale'))
      .toBe('de');

    await page.reload();
    await expect(
      page.getByRole('heading', { name: 'Einstellungen' }),
    ).toBeVisible();
  });
});
