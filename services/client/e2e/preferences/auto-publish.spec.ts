import { expect, test } from '../fixtures/auth.fixture';

test.describe('Auto-publish preference', { tag: '@serial' }, () => {
  test.beforeEach(async ({ api }) => {
    await api.preferences.updatePreferences({
      'image.externalUploadAutoPublish': true,
      'display.theme': 'default',
    });
  });

  test.afterEach(async ({ api }) => {
    await api.preferences.updatePreferences({
      'image.externalUploadAutoPublish': false,
      'display.theme': 'default',
    });
  });

  test('stays visible and enabled when only public images are allowed', async ({
    page,
    preferencesPage,
    layoutControls,
    settingsApi,
  }) => {
    await settingsApi.set('image', { allowOnlyPublicImages: true });

    await preferencesPage.goto();
    await expect(preferencesPage.heading).toBeVisible();

    await expect(preferencesPage.autoPublishSwitch).toBeVisible();
    await expect(preferencesPage.autoPublishSwitch).toHaveAttribute(
      'aria-checked',
      'true',
    );

    await preferencesPage.selectTheme('nord');
    await preferencesPage.save();

    await expect.poll(() => layoutControls.readTheme()).toBe('nord');

    await page.reload();
    await expect(preferencesPage.heading).toBeVisible();

    await expect(preferencesPage.autoPublishSwitch).toHaveAttribute(
      'aria-checked',
      'true',
    );
  });
});
