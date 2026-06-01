import { expect, test } from '../fixtures/auth.fixture';

test.describe('Sidebar', () => {
  test.use({ storageState: 'e2e/.auth/user.json' });

  test('toggles the sidebar and persists the collapsed state across reload', async ({
    page,
    layoutControls,
  }) => {
    await page.goto('/');
    await layoutControls.sidebarTrigger.waitFor({ state: 'visible' });

    const initialCookie = await layoutControls.readSettingCookie('sidebar');
    const initialExpanded = initialCookie
      ? Boolean(JSON.parse(initialCookie).expanded)
      : true;

    await layoutControls.toggleSidebar();

    await expect
      .poll(async () => {
        const cookie = await layoutControls.readSettingCookie('sidebar');
        return cookie ? Boolean(JSON.parse(cookie).expanded) : null;
      })
      .toBe(!initialExpanded);

    await page.reload();
    await layoutControls.sidebarTrigger.waitFor({ state: 'visible' });

    const persisted = await layoutControls.readSettingCookie('sidebar');
    expect(persisted).not.toBeNull();
    expect(Boolean(JSON.parse(persisted as string).expanded)).toBe(
      !initialExpanded,
    );
  });
});
