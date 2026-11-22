import { type Locator, type Page, expect } from '@playwright/test';

import { BasePage } from './BasePage';

export const DEFAULT_CUSTOMIZATION = {
  siteName: 'Slink',
  siteDescription: 'Fast and secure image sharing service',
  logoUrl: '',
  colorMode: 'both',
  themeSelectorEnabled: true,
  defaultTheme: 'default',
  hiddenThemes: [] as string[],
  themes: [] as ReturnType<typeof buildTheme>[],
};

const palette = (color: string) => ({
  background: color,
  foreground: '#101010',
  primary: '#2233ff',
  accent: '#aa22ff',
  success: '#22aa44',
  warning: '#ddaa11',
  danger: '#dd2222',
  info: '#2299dd',
});

export const buildTheme = (
  id: string,
  light = '#fff1e0',
  dark = '#1a0f05',
) => ({
  id,
  name: id.charAt(0).toUpperCase() + id.slice(1),
  light: palette(light),
  dark: palette(dark),
  radius: null as number | null,
  colorMode: 'both',
  css: '',
});

export class ThemeSettingsPage extends BasePage {
  static readonly URL = '/admin/settings/customization';

  readonly list = this.page.locator('[data-slot="theme-list"]');
  readonly editor = this.page.locator('[data-slot="theme-editor"]');
  readonly addButton = this.page
    .locator('form')
    .filter({ has: this.list })
    .getByRole('button', { name: 'Add Theme' });
  readonly nameInput = this.editor.locator('input[name="themeName"]');
  readonly radiusInput = this.editor.locator('input[name="themeRadius"]');
  readonly submitButton = this.editor.locator('button[type="submit"]');
  readonly selectorSwitch = this.page.getByRole('switch', {
    name: 'Theme Selector',
  });
  readonly themeCss = this.editor.locator('textarea[name="themeCss"]');
  readonly colorModeTrigger = this.page.locator(
    'xpath=//h3[normalize-space()="Color Mode"]/ancestor::div[.//*[@data-slot="select-trigger"]][1]//*[@data-slot="select-trigger"]',
  );
  readonly editorColorModeTrigger = this.editor.locator(
    'xpath=.//label[normalize-space()="Color Mode"]/..//*[@data-slot="select-trigger"]',
  );
  readonly dirtyHint = this.page.getByText('Save to apply your theme changes.');

  constructor(page: Page) {
    super(page);
  }

  async goto(query = '') {
    await this.page.goto(`${ThemeSettingsPage.URL}${query}`);
    await expect(this.list).toBeVisible();
  }

  row(id: string): Locator {
    return this.list.locator(`[data-theme-id="${id}"]`);
  }

  action(id: string, name: string): Locator {
    return this.row(id).getByRole('button', { name: new RegExp(`^${name} `) });
  }

  color(scheme: 'light' | 'dark', color: string): Locator {
    return this.editor.locator(`input[name="${scheme}-${color}"]`);
  }

  async openEditor(trigger: Locator) {
    await this.clickUntil(trigger, this.nameInput);
  }

  async applyEditor() {
    await this.submitButton.click();
    await expect(this.editor).toHaveCount(0);
  }

  /** Clicks until the page has hydrated and the list reacted. */
  async clickUntilGone(trigger: Locator) {
    await expect(async () => {
      if (await trigger.isVisible()) {
        await trigger.click();
      }
      await expect(trigger).toHaveCount(0, { timeout: 1000 });
    }).toPass({ timeout: 15000 });
  }

  async save() {
    const saved = this.page.waitForResponse(
      (response) =>
        response.url().endsWith('/api/settings') &&
        response.request().method() === 'POST',
    );
    const reloaded = this.page.waitForEvent('load');

    await this.page
      .locator('form')
      .filter({ has: this.list })
      .getByRole('button', { name: 'Save Changes' })
      .click();

    expect((await saved).ok()).toBe(true);
    await reloaded;
    await expect(this.list).toBeVisible();
  }

  async readToken(name: string): Promise<string> {
    return this.page.evaluate(
      (token) =>
        getComputedStyle(document.documentElement).getPropertyValue(token),
      name,
    );
  }
}
