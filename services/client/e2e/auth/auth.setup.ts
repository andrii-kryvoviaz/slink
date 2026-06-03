import fs from 'fs';
import path from 'path';

import { USER_STORAGE_STATE } from '../../playwright.config';
import { expect, test } from '../fixtures/auth.fixture';

test('authenticate', async ({ page, testUser, loginPage }) => {
  await loginPage.login(testUser.username, testUser.password);
  await expect(page).not.toHaveURL(/\/profile\/login/);

  fs.mkdirSync(path.dirname(USER_STORAGE_STATE), { recursive: true });

  await page.context().storageState({ path: USER_STORAGE_STATE });
});
