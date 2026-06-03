import { expect, test } from '../fixtures/auth.fixture';

test.describe('Locale preference', { tag: '@serial' }, () => {
  test.use({ storageState: 'e2e/.auth/serial.json' });

  test.beforeEach(async ({ localeHelper }) => {
    await localeHelper.reset();
  });

  test.afterEach(async ({ localeHelper }) => {
    await localeHelper.reset();
  });

  test('changing the display language translates the UI and persists', async ({
    page,
    preferencesPage,
    localeHelper,
  }) => {
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
