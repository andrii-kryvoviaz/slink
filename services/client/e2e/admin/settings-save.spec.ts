import { expect, test } from '../fixtures/auth.fixture';

test.describe('Admin security settings', { tag: '@serial' }, () => {
  test('toggles a security setting and persists it across reload', async ({
    page,
    adminSettingsPage,
    api,
  }) => {
    await adminSettingsPage.gotoSecurity();
    await expect(adminSettingsPage.heading).toBeVisible();
    await expect(adminSettingsPage.allowRegistrationSwitch).toBeVisible();

    const before =
      await adminSettingsPage.allowRegistrationSwitch.getAttribute(
        'aria-checked',
      );
    const expected = before === 'true' ? 'false' : 'true';

    await adminSettingsPage.toggleSetting('allowRegistration');
    await adminSettingsPage.saveUserSettings();

    await expect
      .poll(async () => {
        const current = await api.settings.getSettings();
        return String(Boolean(current.user?.allowRegistration));
      })
      .toBe(expected);

    await page.reload();
    await expect(adminSettingsPage.allowRegistrationSwitch).toBeVisible();

    await expect(adminSettingsPage.allowRegistrationSwitch).toHaveAttribute(
      'aria-checked',
      expected,
    );
  });
});
