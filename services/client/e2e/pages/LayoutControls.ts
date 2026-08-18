import { type Page, expect } from '@playwright/test';

import { BasePage } from './BasePage';

export class LayoutControls extends BasePage {
  readonly modeToggle = this.page
    .getByRole('button', { name: /Switch to (dark|light) mode/ })
    .last();
  readonly sidebarTrigger = this.page.locator('[data-sidebar="trigger"]');

  constructor(page: Page) {
    super(page);
  }

  async toggleMode() {
    const before = await this.isDark();
    await expect(async () => {
      await this.modeToggle.click();
      expect(await this.isDark()).not.toBe(before);
    }).toPass({ timeout: 15000 });
  }

  async setMode(mode: 'light' | 'dark') {
    await expect(async () => {
      if ((await this.readModeClass()) !== mode) {
        await this.modeToggle.click();
      }
      expect(await this.readModeClass()).toBe(mode);
    }).toPass({ timeout: 15000 });
  }

  async readModeClass(): Promise<string | null> {
    return this.page.evaluate(() => {
      const modes = ['light', 'dark', 'system'];
      const found = modes.find((mode) =>
        document.documentElement.classList.contains(mode),
      );
      return found ?? null;
    });
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

  async readColorScheme(): Promise<string> {
    return this.page.evaluate(
      () => getComputedStyle(document.documentElement).colorScheme,
    );
  }

  async readTheme(): Promise<string | null> {
    return this.page.evaluate(
      () => document.documentElement.dataset.theme ?? null,
    );
  }

  async readSurfaceBackground(): Promise<string> {
    return this.page.evaluate(() => {
      const { backgroundColor, backgroundImage } = getComputedStyle(
        document.body,
      );
      return `${backgroundColor} ${backgroundImage}`;
    });
  }

  async setThemeCookie(value: string) {
    await this.page.context().addCookies([
      {
        name: 'settings.theme',
        value,
        url: process.env.E2E_BASE_URL ?? 'http://localhost:3100',
      },
    ]);
  }

  async readSettingCookie(key: string) {
    const cookies = await this.page.context().cookies();
    const match = cookies.find((cookie) => cookie.name === `settings.${key}`);
    return match?.value ?? null;
  }
}
