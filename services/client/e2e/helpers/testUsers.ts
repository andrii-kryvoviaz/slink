const TEST_PASSWORD = 'E2eTest123!';

export const SERIAL_PROJECT = 'shared-state-mutating';

export type TestUser = { email: string; username: string; password: string };

export const ADMIN_USER: TestUser = {
  email: 'e2e@test.local',
  username: 'e2e',
  password: TEST_PASSWORD,
};

export function workerUser(parallelIndex: number): TestUser {
  return {
    email: `e2e-worker-${parallelIndex}@test.local`,
    username: `e2eWorker${parallelIndex}`,
    password: TEST_PASSWORD,
  };
}

export function resolveTestUser(
  projectName: string,
  parallelIndex: number,
): TestUser {
  if (projectName === SERIAL_PROJECT) {
    return ADMIN_USER;
  }

  return workerUser(parallelIndex);
}
