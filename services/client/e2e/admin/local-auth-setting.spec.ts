import { expect, test } from '../fixtures/auth.fixture';
import type { ApiClient } from '../helpers/api';
import { createSsoProvider, deleteSsoProviders } from '../helpers/ssoProviders';
import type { AdminSettingsPage } from '../pages/AdminSettingsPage';

const SLUG_PREFIX = 'e2e-local-auth-switch';
const LOCAL_AUTH = 'localAuthEnabled';
const ENV_DEFAULT = 'true';
const VIOLATION_MESSAGE = 'Local authentication cannot be changed right now';

const openLocalAuthSwitch = async (adminSettingsPage: AdminSettingsPage) => {
  await adminSettingsPage.gotoSecurity();
  await expect(adminSettingsPage.heading).toBeVisible();
  await expect(adminSettingsPage.localAuthEnabledSwitch).toBeVisible();
};

const expectPublicLocalAuth = (api: ApiClient, expected: boolean) =>
  expect
    .poll(
      async () =>
        (await api.settings.getPublicSettings()).user.localAuthEnabled,
    )
    .toBe(expected);

test.describe(
  'Admin local authentication switch',
  { tag: ['@serial', '@sc-1133'] },
  () => {
    test.beforeEach(({ api }) => createSsoProvider(api, SLUG_PREFIX));

    test.afterEach(async ({ api }) => {
      const { user } = await api.settings.getSettings();

      await api.settings.updateSettings('user', {
        ...user,
        localAuthEnabled: true,
      });
      await deleteSsoProviders(api, SLUG_PREFIX);
    });

    for (const stored of [true, false]) {
      test(`reflects a stored value of ${stored}`, async ({
        settingsApi,
        adminSettingsPage,
      }) => {
        await settingsApi.set('user', { localAuthEnabled: stored });

        await openLocalAuthSwitch(adminSettingsPage);

        await expect(adminSettingsPage.localAuthEnabledSwitch).toHaveAttribute(
          'aria-checked',
          String(stored),
        );
      });
    }

    test('explains that turning it off leaves sign-in to SSO providers', async ({
      adminSettingsPage,
    }) => {
      await openLocalAuthSwitch(adminSettingsPage);

      await expect(adminSettingsPage.settingRow(LOCAL_AUTH)).toContainText(
        /SSO/,
      );
    });

    test('reports each saved value publicly and keeps it after a reload', async ({
      page,
      api,
      adminSettingsPage,
    }) => {
      await openLocalAuthSwitch(adminSettingsPage);

      await adminSettingsPage.setSwitch(LOCAL_AUTH, false);
      await adminSettingsPage.saveUserSettings();

      await expectPublicLocalAuth(api, false);
      await page.reload();
      await expect(adminSettingsPage.localAuthEnabledSwitch).toHaveAttribute(
        'aria-checked',
        'false',
      );

      await adminSettingsPage.setSwitch(LOCAL_AUTH, true);
      await adminSettingsPage.saveUserSettings();

      await expectPublicLocalAuth(api, true);
      await page.reload();
      await expect(adminSettingsPage.localAuthEnabledSwitch).toHaveAttribute(
        'aria-checked',
        'true',
      );
    });

    test('resets a saved value to the ENV default', async ({
      api,
      settingsApi,
      adminSettingsPage,
    }) => {
      await settingsApi.set('user', { localAuthEnabled: false });
      await openLocalAuthSwitch(adminSettingsPage);
      await expect(adminSettingsPage.localAuthEnabledSwitch).toHaveAttribute(
        'aria-checked',
        'false',
      );

      await adminSettingsPage.resetSetting(LOCAL_AUTH);

      await expect(adminSettingsPage.localAuthEnabledSwitch).toHaveAttribute(
        'aria-checked',
        ENV_DEFAULT,
      );

      await adminSettingsPage.saveUserSettings();

      await expectPublicLocalAuth(api, ENV_DEFAULT === 'true');
    });

    test('shows a save violation on the switch row only', async ({
      page,
      adminSettingsPage,
    }) => {
      await page.route('**/api/settings', (route) => {
        if (route.request().method() !== 'POST') return route.continue();
        return route.fulfill({
          status: 400,
          contentType: 'application/json',
          body: JSON.stringify({
            error: {
              message: 'Specification Error',
              violations: [
                { property: LOCAL_AUTH, message: VIOLATION_MESSAGE },
              ],
            },
          }),
        });
      });

      await openLocalAuthSwitch(adminSettingsPage);
      await adminSettingsPage.setSwitch(LOCAL_AUTH, false);
      await adminSettingsPage.saveUserSettings();

      await expect(adminSettingsPage.settingRow(LOCAL_AUTH)).toContainText(
        VIOLATION_MESSAGE,
      );
      await expect(
        adminSettingsPage.settingRow('allowRegistration'),
      ).not.toContainText(VIOLATION_MESSAGE);
    });
  },
);
