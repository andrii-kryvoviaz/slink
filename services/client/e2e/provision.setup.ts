import { test } from '@playwright/test';

import { ApiClient } from './helpers/api';
import { ensureUser, grantRole } from './helpers/slink';
import { ADMIN_USER } from './helpers/testUsers';

test('provision', async () => {
  ensureUser({ ...ADMIN_USER, active: true });
  console.log('[e2e] Test user provisioned and active');

  grantRole(ADMIN_USER.email, 'ROLE_ADMIN');
  console.log('[e2e] Admin role granted');

  const api = await ApiClient.createForUser(
    ADMIN_USER.username,
    ADMIN_USER.password,
  );

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
