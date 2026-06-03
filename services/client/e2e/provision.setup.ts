import { test } from '@playwright/test';
import fs from 'fs';
import path from 'path';

import { API_TOKEN_PATH, ApiClient } from './helpers/api';
import { ensureUser, grantRole } from './helpers/slink';

test('provision', async () => {
  ensureUser({
    email: 'e2e@test.local',
    username: 'e2e',
    password: 'E2eTest123!',
    active: true,
  });
  console.log('[e2e] Test user provisioned and active');

  grantRole('e2e@test.local', 'ROLE_ADMIN');
  console.log('[e2e] Admin role granted');

  const api = await ApiClient.create();

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

  fs.mkdirSync(path.dirname(API_TOKEN_PATH), { recursive: true });
  fs.writeFileSync(API_TOKEN_PATH, api.token);
  console.log('[e2e] API token cached');
});
