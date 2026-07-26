import { expect, test } from '../fixtures/auth.fixture';
import { type Account, unique } from '../helpers/accounts';
import type { ApiClient } from '../helpers/api';
import { provisionUser } from '../helpers/provisioning';

const PASSWORD = 'TestPass123!';

type Target = {
  account: Account;
  api: ApiClient;
  id: string;
};

const createTarget = async (
  admin: ApiClient,
  label: string,
): Promise<Target> => {
  const account = unique(label);
  const api = await provisionUser(account);
  const { id } = await admin.users.findUserByEmail(account.email);

  return { account, api, id };
};

test.describe('Admin user deletion', { tag: '@serial' }, () => {
  test('soft deletes in place and drops the row on reload', async ({
    page,
    api,
    adminUsersPage,
  }) => {
    const target = await createTarget(api, 'sdel');

    await adminUsersPage.goto();
    const row = await adminUsersPage.findRow(target.account.username);

    await expect(row.getByText(target.account.email)).toBeVisible();

    await adminUsersPage.openDeleteConfirmation(row);

    await expect(
      adminUsersPage.menu.getByText(target.account.email),
    ).toBeVisible();
    await expect(adminUsersPage.purgeSwitch).toHaveAttribute(
      'aria-checked',
      'false',
    );

    await adminUsersPage.confirmDeletion();

    await expect(adminUsersPage.statusBadgeIn(row, 'Deleted')).toBeVisible();
    await expect(row.getByText(target.account.email)).toHaveCount(0);

    await page.reload();
    await adminUsersPage.waitForFullList();
    await expect(adminUsersPage.rowFor(target.account.username)).toHaveCount(0);
  });

  test('purges from the row actions and removes the row immediately', async ({
    api,
    adminUsersPage,
  }) => {
    const target = await createTarget(api, 'prow');

    await adminUsersPage.goto();
    const row = await adminUsersPage.findRow(target.account.username);

    await adminUsersPage.openDeleteConfirmation(row);
    await adminUsersPage.setPurge(true);
    await adminUsersPage.confirmDeletion();

    await expect(row).toHaveCount(0);
    await expect(adminUsersPage.rows.first()).toBeVisible();
  });

  test('warns about irreversible data loss only while purge is enabled', async ({
    api,
    adminUsersPage,
  }) => {
    const target = await createTarget(api, 'warn');

    await adminUsersPage.goto();
    const row = await adminUsersPage.findRow(target.account.username);

    await adminUsersPage.openDeleteConfirmation(row);

    await expect(adminUsersPage.purgeWarning).toBeHidden();

    await adminUsersPage.setPurge(true);
    await expect(adminUsersPage.purgeWarning).toBeVisible();

    await adminUsersPage.setPurge(false);
    await expect(adminUsersPage.purgeWarning).toBeHidden();

    await api.users.purgeUser(target.id);
  });

  test('renders comments of a purged author as deleted', async ({
    page,
    api,
    actor,
    explorePage,
  }) => {
    const target = await createTarget(api, 'cmnt');
    const owner = await actor('owner');
    const imageId = await owner.content.uploadImage({ isPublic: true });
    const content = `Comment by a purged author ${Date.now()}`;

    await target.api.content.createComment(imageId, content);
    await api.users.purgeUser(target.id);

    await page.goto(`/explore?post=${imageId}`);
    await expect(explorePage.viewer).toBeVisible();

    const comment = explorePage.viewer
      .locator('div')
      .filter({ hasText: content })
      .last();

    await expect(comment).toBeVisible();
    await expect(comment.getByText('[deleted]')).toBeVisible();
    await expect(comment.getByText(target.account.username)).toHaveCount(0);
  });

  test('removes images of a purged user from explore', async ({
    api,
    explorePage,
  }) => {
    const target = await createTarget(api, 'imgs');
    await target.api.content.uploadImage({ isPublic: true });

    await explorePage.goto();
    await explorePage.search(target.account.username);
    await expect(explorePage.feedItems).toHaveCount(1);

    await api.users.purgeUser(target.id);

    await explorePage.goto();
    await explorePage.search(target.account.username);
    await expect(explorePage.feedItems).toHaveCount(0);
  });

  test(
    'frees the email of a purged user for a new signup',
    { tag: '@anonymous' },
    async ({ api, settingsApi, signupPage }) => {
      const target = await createTarget(api, 'mail');
      await api.users.purgeUser(target.id);

      await settingsApi.set('user', { allowRegistration: true });

      const username = `e2email${Date.now().toString(36)}`;

      await signupPage.signup({
        username,
        email: target.account.email,
        password: PASSWORD,
        confirm: PASSWORD,
      });

      await expect(signupPage.page).toHaveURL(/\/profile\/awaiting-approval/, {
        timeout: 15000,
      });

      await expect
        .poll(
          async () => {
            const user = await api.users.findUserByEmail(target.account.email);
            return user?.username;
          },
          { timeout: 15000 },
        )
        .toBe(username);
    },
  );
});
