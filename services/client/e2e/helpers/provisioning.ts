import type { Account } from './accounts';
import { ApiClient } from './api';
import { slink } from './slink';

export function ensureUser(user: {
  email: string;
  username: string;
  password: string;
  active?: boolean;
}): void {
  const args = [
    'user:create',
    '--if-not-exists',
    `--email=${user.email}`,
    `--username=${user.username}`,
    '-p',
    user.password,
  ];

  if (user.active) {
    args.push('-a');
  }

  slink(...args);
}

export function grantRole(email: string, role: string): void {
  slink('user:grant:role', `--email=${email}`, role);
}

export async function provisionUser(
  account: Account,
  opts?: { admin?: boolean },
): Promise<ApiClient> {
  ensureUser({ ...account, active: true });

  if (opts?.admin) {
    grantRole(account.email, 'ROLE_ADMIN');
  }

  const api = await ApiClient.createForUser(account.username, account.password);
  await api.preferences.updatePreferences({ 'display.language': 'en' });

  return api;
}
