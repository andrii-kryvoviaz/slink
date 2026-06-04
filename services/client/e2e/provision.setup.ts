import { test } from '@playwright/test';

import { admin } from './helpers/accounts';
import { ApiClient } from './helpers/api';
import { ensureUser, grantRole } from './helpers/provisioning';

test('provision', async () => {
  ensureUser({ ...admin, active: true });
  console.log('[e2e] Test user provisioned and active');

  grantRole(admin.email, 'ROLE_ADMIN');
  console.log('[e2e] Admin role granted');

  const api = await ApiClient.createForUser(admin.username, admin.password);

  const current = await api.settings.getSettings();

  await api.settings.updateSettings('access', {
    ...current.access,
    allowGuestUploads: false,
    allowUnauthenticatedAccess: true,
    requireSsl: false,
    requireAuthForMediaShares: false,
    requireAuthForCollectionShares: false,
  });

  await api.settings.updateSettings('user', {
    ...current.user,
    allowRegistration: true,
    approvalRequired: true,
    password: {
      ...current.user?.password,
      minLength: 8,
    },
  });

  console.log('[e2e] Baseline settings established');

  await api.preferences.updatePreferences({ 'display.language': 'en' });
  console.log('[e2e] Locale reset to English');
});
