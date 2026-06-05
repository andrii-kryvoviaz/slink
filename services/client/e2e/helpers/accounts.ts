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

export function unique(prefix: string): Account {
  const suffix = `${Date.now()}${Math.floor(Math.random() * 1000)}`;
  return makeAccount(
    `e2e-${prefix}-${suffix}@test.local`,
    `e2e${prefix}${suffix}`,
  );
}
