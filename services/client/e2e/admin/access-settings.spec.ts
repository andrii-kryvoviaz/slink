import { expect, test } from '../fixtures/auth.fixture';

const BASELINE_ACCESS = {
  allowGuestUploads: false,
  allowUnauthenticatedAccess: true,
  requireAuthForMediaShares: false,
  requireAuthForCollectionShares: false,
  requireSsl: false,
};

test.describe('Admin access settings', { tag: '@serial' }, () => {
  test.beforeEach(async ({ adminSettingsPage }) => {
    await adminSettingsPage.gotoSecurity();
    await expect(adminSettingsPage.heading).toBeVisible();
    await expect(adminSettingsPage.allowGuestUploadsSwitch).toBeVisible();
  });

  test.afterEach(async ({ api }) => {
    await api.settings.updateSettings('access', BASELINE_ACCESS);
  });

  test('shows guest upload warning when guest upload is on and guest view off', async ({
    adminSettingsPage,
  }) => {
    await adminSettingsPage.setSwitch('accessAllowGuestUploads', true);
    await adminSettingsPage.setSwitch(
      'accessAllowUnauthenticatedAccess',
      false,
    );

    await expect(adminSettingsPage.guestUploadWarningNotice).toBeVisible();
  });

  test('shows guest upload warning when both guest upload and guest view are on', async ({
    adminSettingsPage,
  }) => {
    await adminSettingsPage.setSwitch('accessAllowGuestUploads', true);
    await adminSettingsPage.setSwitch('accessAllowUnauthenticatedAccess', true);

    await expect(adminSettingsPage.guestUploadWarningNotice).toBeVisible();
  });

  test('hides guest upload warning when guest upload is off', async ({
    adminSettingsPage,
  }) => {
    await adminSettingsPage.setSwitch('accessAllowGuestUploads', false);

    await expect(adminSettingsPage.guestUploadWarningNotice).toBeHidden();
  });

  test('shows the embed notice under media share access', async ({
    adminSettingsPage,
  }) => {
    await expect(adminSettingsPage.embedNotice).toBeVisible();

    await adminSettingsPage.setSwitch('accessRequireAuthForMediaShares', true);
    await expect(adminSettingsPage.embedNotice).toBeVisible();
  });

  test('persists a mix of access flags across reload', async ({
    page,
    adminSettingsPage,
    api,
  }) => {
    await adminSettingsPage.setSwitch('accessAllowGuestUploads', true);
    await adminSettingsPage.setSwitch('accessRequireAuthForMediaShares', true);
    await adminSettingsPage.setSwitch(
      'accessAllowUnauthenticatedAccess',
      false,
    );

    await adminSettingsPage.saveAccessSettings();

    await expect
      .poll(async () => {
        const current = await api.settings.getSettings();
        const access = current.access ?? {};
        return (
          Boolean(access.allowGuestUploads) === true &&
          Boolean(access.requireAuthForMediaShares) === true &&
          Boolean(access.allowUnauthenticatedAccess) === false
        );
      })
      .toBe(true);

    await page.reload();
    await expect(adminSettingsPage.allowGuestUploadsSwitch).toBeVisible();

    await expect(adminSettingsPage.allowGuestUploadsSwitch).toHaveAttribute(
      'aria-checked',
      'true',
    );
    await expect(
      adminSettingsPage.requireAuthForMediaSharesSwitch,
    ).toHaveAttribute('aria-checked', 'true');
    await expect(
      adminSettingsPage.allowUnauthenticatedAccessSwitch,
    ).toHaveAttribute('aria-checked', 'false');
  });

  test('resets an access setting to its default value', async ({
    adminSettingsPage,
  }) => {
    await adminSettingsPage.setSwitch('accessAllowGuestUploads', true);
    await expect(adminSettingsPage.allowGuestUploadsSwitch).toHaveAttribute(
      'aria-checked',
      'true',
    );

    await adminSettingsPage.resetAccessSetting('accessAllowGuestUploads');

    await expect(adminSettingsPage.allowGuestUploadsSwitch).toHaveAttribute(
      'aria-checked',
      'false',
    );
  });
});
