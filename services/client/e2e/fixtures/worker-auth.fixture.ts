import { test as base, expect } from '@playwright/test';
import fs from 'fs';
import path from 'path';

import { ApiClient } from '../helpers/api';
import { provisionUser, signInContext } from '../helpers/auth';
import { type TestUser, resolveTestUser } from '../helpers/testUsers';

type WorkerAuthFixtures = {
  workerUserReady: { user: TestUser; api: ApiClient };
  workerApi: ApiClient;
  workerStorageState: string;
};

export const test = base.extend<{}, WorkerAuthFixtures>({
  workerUserReady: [
    async ({}, use) => {
      const user = resolveTestUser(
        test.info().project.name,
        test.info().parallelIndex,
      );

      const api = await provisionUser(user, { admin: true });

      await use({ user, api });
    },
    { scope: 'worker' },
  ],

  workerApi: [
    async ({ workerUserReady }, use) => {
      await use(workerUserReady.api);
    },
    { scope: 'worker' },
  ],

  workerStorageState: [
    async ({ workerUserReady, browser }, use) => {
      const fileName = path.resolve(
        test.info().project.outputDir,
        `.auth/${test.info().parallelIndex}.json`,
      );

      if (fs.existsSync(fileName)) {
        await use(fileName);
        return;
      }

      const context = await signInContext(browser, workerUserReady.user);
      await context.storageState({ path: fileName });
      await context.close();

      await use(fileName);
    },
    { scope: 'worker' },
  ],

  storageState: async ({ workerStorageState }, use) => {
    const anonymous = test.info().tags.includes('@anonymous');
    await use(anonymous ? undefined : workerStorageState);
  },
});

export { expect };
