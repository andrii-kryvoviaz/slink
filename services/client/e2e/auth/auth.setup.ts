import fs from 'fs';
import path from 'path';

import { expect, test } from '../fixtures/auth.fixture';

test('authenticate', async ({ page, testUser, loginPage }) => {
  await loginPage.login(testUser.username, testUser.password);
  await expect(page).not.toHaveURL(/\/profile\/login/);

  const authDir = path.join(process.cwd(), 'e2e', '.auth');
  fs.mkdirSync(authDir, { recursive: true });

  await page.context().storageState({ path: path.join(authDir, 'user.json') });
});
