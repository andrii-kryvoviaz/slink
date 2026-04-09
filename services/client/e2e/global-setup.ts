import { execFileSync } from 'child_process';

import { AdminApiClient } from './helpers/AdminApiClient';

const DOCKER_ARGS = [
  'compose',
  '-p',
  'slink-e2e',
  'exec',
  '-T',
  'slink',
  'slink',
];

function slink(...args: string[]) {
  execFileSync('docker', [...DOCKER_ARGS, ...args]);
}

export default async function globalSetup() {
  try {
    slink(
      'user:create',
      '--email=e2e@test.local',
      '--username=e2e',
      '-p',
      'E2eTest123!',
      '-a',
    );
    console.log('[e2e] Test user created');
  } catch {
    slink('user:activate', '--email=e2e@test.local');
    console.log('[e2e] Test user activated');
  }

  try {
    slink('user:grant:role', '--email=e2e@test.local', 'ROLE_ADMIN');
    console.log('[e2e] Admin role granted');
  } catch {
    console.log('[e2e] Admin role already granted');
  }

  const admin = await AdminApiClient.create();
  process.env.E2E_ADMIN_TOKEN = admin.token;
  console.log('[e2e] Admin API token cached');
}
