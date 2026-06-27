export type Account = { email: string; username: string; password: string };

const PASSWORD = 'E2eTest123!';

const makeAccount = (email: string, username: string): Account => ({
  email,
  username,
  password: PASSWORD,
});

export const admin: Account = makeAccount('e2e@test.local', 'e2e');

export function worker(index: number): Account {
  return makeAccount(`e2e-worker-${index}@test.local`, `e2eWorker${index}`);
}

const USERNAME_MAX_LENGTH = 30;
const USERNAME_NAMESPACE = 'e2e';

export function unique(prefix: string): Account {
  const rand = Math.floor(Math.random() * 1296).toString(36);
  const suffix = `${Date.now().toString(36)}${rand}`;

  const room = USERNAME_MAX_LENGTH - USERNAME_NAMESPACE.length - suffix.length;
  const usernamePrefix = prefix.slice(0, Math.max(0, room));
  const username = `${USERNAME_NAMESPACE}${usernamePrefix}${suffix}`;

  return makeAccount(`e2e-${prefix}-${suffix}@test.local`, username);
}
