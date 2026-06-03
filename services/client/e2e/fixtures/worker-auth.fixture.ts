import { test as base, expect } from '@playwright/test';
import fs from 'fs';
import path from 'path';

import { ApiClient } from '../helpers/api';
import { ensureUser, grantRole } from '../helpers/slink';
import { type TestUser, resolveTestUser } from '../helpers/testUsers';
import { LoginPage } from '../pages/LoginPage';

type WorkerAuthFixtures = {
  workerUserReady: TestUser;
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

      ensureUser({ ...user, active: true });
      grantRole(user.email, 'ROLE_ADMIN');

      const api = await ApiClient.createForUser(user.username, user.password);
      await api.preferences.updatePreferences({ 'display.language': 'en' });

      await use(user);
    },
    { scope: 'worker' },
  ],

  workerApi: [
    async ({ workerUserReady }, use) => {
      const api = await ApiClient.createForUser(
        workerUserReady.username,
        workerUserReady.password,
      );
      await use(api);
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

      const page = await browser.newPage({
        storageState: undefined,
        baseURL:
          test.info().project.use.baseURL ??
          process.env.E2E_BASE_URL ??
          'http://localhost:3100',
      });
      const loginPage = new LoginPage(page);
      await loginPage.login(workerUserReady.username, workerUserReady.password);
      await expect(page).not.toHaveURL(/\/profile\/login/);

      await page.context().storageState({ path: fileName });
      await page.close();

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
