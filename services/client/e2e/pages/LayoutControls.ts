import { type Page, expect } from '@playwright/test';

import { BasePage } from './BasePage';

export class LayoutControls extends BasePage {
  readonly themeToggle = this.page
    .getByRole('button', { name: /Switch to (dark|light) mode/ })
    .last();
  readonly sidebarTrigger = this.page.locator('[data-sidebar="trigger"]');

  constructor(page: Page) {
    super(page);
  }

  async toggleTheme() {
    const before = await this.isDark();
    await expect(async () => {
      await this.themeToggle.click();
      expect(await this.isDark()).not.toBe(before);
    }).toPass({ timeout: 15000 });
  }

  async isDark() {
    return this.page.evaluate(() =>
      document.documentElement.classList.contains('dark'),
    );
  }

  async toggleSidebar() {
    const before = await this.readSidebarExpanded();
    await expect(async () => {
      await this.sidebarTrigger.click();
      expect(await this.readSidebarExpanded()).not.toBe(before);
    }).toPass({ timeout: 15000 });
  }

  async readSidebarExpanded(): Promise<boolean | null> {
    const cookie = await this.readSettingCookie('sidebar');
    if (!cookie) {
      return null;
    }
    return Boolean(JSON.parse(cookie).expanded);
  }

  async readSettingCookie(key: string) {
    const cookies = await this.page.context().cookies();
    const match = cookies.find((cookie) => cookie.name === `settings.${key}`);
    return match?.value ?? null;
  }
}
