import { expect, test } from '../fixtures/auth.fixture';

const SMB_CREDENTIALS = {
  host: '192.168.50.10',
  share: 'e2e-uploads',
  workgroup: 'E2EGROUP',
  username: 'e2e-smb-user',
  password: 'e2e-smb-secret',
};

const restoreToLocal = async (api: {
  settings: {
    getSettings: () => Promise<any>;
    updateSettings: (category: string, settings: object) => Promise<unknown>;
  };
}) => {
  const current = await api.settings.getSettings();
  const storage = current.storage ?? {};
  await api.settings.updateSettings('storage', {
    ...storage,
    provider: 'local',
  });
};

test.describe('Admin storage settings', { tag: '@serial' }, () => {
  test.beforeEach(async ({ storageSettingsPage }) => {
    await storageSettingsPage.goto();
    await expect(storageSettingsPage.heading).toBeVisible();
  });

  test.afterEach(async ({ api }) => {
    await restoreToLocal(api);
  });

  test('reveals SMB fields and saves an SMB provider without a validation error', async ({
    storageSettingsPage,
    api,
  }) => {
    await storageSettingsPage.selectProvider('Network Storage (SMB)');

    await expect(storageSettingsPage.smbHost).toBeVisible();
    await expect(storageSettingsPage.smbShare).toBeVisible();
    await expect(storageSettingsPage.smbWorkgroup).toBeVisible();
    await expect(storageSettingsPage.smbUsername).toBeVisible();
    await expect(storageSettingsPage.smbPassword).toBeVisible();

    await storageSettingsPage.fillSmbCredentials(SMB_CREDENTIALS);
    await storageSettingsPage.save();

    await expect
      .poll(async () => {
        const current = await api.settings.getSettings();
        const storage = current.storage ?? {};
        const smb = storage.adapter?.smb ?? {};
        return storage.provider === 'smb' && smb.host === SMB_CREDENTIALS.host;
      })
      .toBe(true);

    await expect(storageSettingsPage.errorToast).toBeHidden();
  });

  test('round-trips the SMB workgroup value across a reload', async ({
    page,
    storageSettingsPage,
  }) => {
    await storageSettingsPage.selectProvider('Network Storage (SMB)');
    await storageSettingsPage.fillSmbCredentials(SMB_CREDENTIALS);
    await storageSettingsPage.save();

    await page.reload();
    await expect(storageSettingsPage.heading).toBeVisible();
    await expect(storageSettingsPage.smbWorkgroup).toBeVisible();

    await expect(storageSettingsPage.smbWorkgroup).toHaveValue(
      SMB_CREDENTIALS.workgroup,
    );
  });
});
