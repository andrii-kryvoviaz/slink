import { test as base, expect } from '@playwright/test';
import fs from 'fs';
import path from 'path';

import { type Account, admin, worker } from '../helpers/accounts';
import { ApiClient } from '../helpers/api';
import { provisionUser } from '../helpers/provisioning';
import { signInContext } from '../helpers/session';

export const SERIAL_PROJECT = 'shared-state-mutating';

export function accountForWorker(
  projectName: string,
  parallelIndex: number,
): Account {
  return projectName === SERIAL_PROJECT ? admin : worker(parallelIndex);
}

type WorkerAuthFixtures = {
  workerUserReady: { user: Account; api: ApiClient };
  workerApi: ApiClient;
  workerStorageState: string;
};

export const test = base.extend<{}, WorkerAuthFixtures>({
  workerUserReady: [
    async ({}, use) => {
      const user = accountForWorker(
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
