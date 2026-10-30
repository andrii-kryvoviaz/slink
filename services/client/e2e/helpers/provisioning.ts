import { type Account, admin } from './accounts';
import { ApiClient } from './api';
import { slink } from './slink';

const ADMIN_ROLE = 'ROLE_ADMIN';

type UserRecord = { id: string; status: string; roles: string[] };

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

async function registerUser(
  moderator: ApiClient,
  account: Account,
): Promise<UserRecord> {
  const signUp = await moderator.users.signUp(account);
  const user: UserRecord | null = await moderator.users.findUserByEmail(
    account.email,
  );

  if (!user) {
    throw new Error(
      `Sign-up of ${account.username} failed: ${signUp.status} ${JSON.stringify(signUp.data)}`,
    );
  }

  return user;
}

export async function provisionUser(
  account: Account,
  opts?: { admin?: boolean },
): Promise<ApiClient> {
  const moderator = await ApiClient.createForUser(
    admin.username,
    admin.password,
  );
  const user = await registerUser(moderator, account);

  if (user.status !== 'active') {
    await moderator.users.changeUserStatus(user.id, 'active');
  }

  if (opts?.admin && !user.roles.includes(ADMIN_ROLE)) {
    await moderator.users.grantRole(user.id, ADMIN_ROLE);
  }

  const api = await ApiClient.createForUser(account.username, account.password);
  await api.preferences.updatePreferences({ 'display.language': 'en' });

  return api;
}
