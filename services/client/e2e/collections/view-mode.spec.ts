import { expect, test } from '../fixtures/auth.fixture';

test.describe('Collections view mode', () => {
  test.use({ storageState: 'e2e/.auth/user.json' });

  test('switches to table mode and persists the choice across reload', async ({
    page,
    collectionsPage,
    layoutControls,
    api,
  }) => {
    await api.content.createCollection({ name: `E2E ViewMode ${Date.now()}` });

    await collectionsPage.goto();
    await expect(collectionsPage.heading).toBeVisible();

    await collectionsPage.setViewMode('table');

    await expect(page.getByRole('table')).toBeVisible();
    await expect
      .poll(async () => {
        const cookie = await layoutControls.readSettingCookie('collections');
        return cookie ? JSON.parse(cookie).viewMode : null;
      })
      .toBe('table');

    await page.reload();
    await expect(collectionsPage.heading).toBeVisible();

    await expect(page.getByRole('table')).toBeVisible();
    const persisted = await layoutControls.readSettingCookie('collections');
    expect(persisted).not.toBeNull();
    expect(JSON.parse(persisted as string).viewMode).toBe('table');
  });
});
