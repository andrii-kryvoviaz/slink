import type { BrowserContext, Page } from '@playwright/test';

import { expect, test } from '../fixtures/auth.fixture';
import { unique } from '../helpers/accounts';
import type { ApiClient } from '../helpers/api';
import { provisionUser } from '../helpers/provisioning';
import { signInContext } from '../helpers/session';
import { PreferencesPage } from '../pages/PreferencesPage';

async function saveAndReload(page: Page, preferencesPage: PreferencesPage) {
  const saved = page.waitForResponse(
    (response) =>
      response.url().includes('?/updatePreferences') &&
      response.request().method() === 'POST',
  );

  await preferencesPage.save();
  expect((await saved).ok()).toBe(true);

  await page.reload();
  await expect(preferencesPage.heading).toBeVisible();
}

test.describe('Default license', { tag: '@serial' }, () => {
  let context: BrowserContext;
  let page: Page;
  let preferencesPage: PreferencesPage;
  let owner: ApiClient;
  const pageErrors: Error[] = [];
  const consoleErrors: string[] = [];

  test.beforeAll(async ({ browser }) => {
    const account = unique('license');
    owner = await provisionUser(account);
    await owner.preferences.updatePreferences({ 'license.default': 'cc-by' });

    context = await signInContext(browser, account);
    page = await context.newPage();
    preferencesPage = new PreferencesPage(page);

    const isUpdateCheckNoise = (text: string) =>
      text.includes('check for updates') ||
      text.includes('GitHub API') ||
      text.includes('status of 403');

    page.on('pageerror', (error) => pageErrors.push(error));
    page.on('console', (message) => {
      if (message.type() === 'error' && !isUpdateCheckNoise(message.text())) {
        consoleErrors.push(message.text());
      }
    });
  });

  test.afterAll(async () => {
    await context?.close();
  });

  test('owner clears a stored license with No license', async ({
    settingsApi,
  }) => {
    await settingsApi.set('image', { enableLicensing: true });

    await preferencesPage.goto();
    await expect(preferencesPage.heading).toBeVisible();

    const seededTitle = (
      (await preferencesPage.licenseTrigger.textContent()) ?? ''
    ).trim();
    expect(seededTitle).not.toBe('No license');
    expect(seededTitle).not.toBe('Select a license...');

    const noLicenseOption = preferencesPage.page.getByRole('option').first();
    await preferencesPage.clickUntil(
      preferencesPage.licenseTrigger,
      noLicenseOption,
    );
    await expect(noLicenseOption).toHaveText('No license');

    await noLicenseOption.click();
    await expect(preferencesPage.page.getByRole('option')).toHaveCount(0);

    await expect(page.locator('input[name="license.default"]')).toHaveValue('');

    await saveAndReload(page, preferencesPage);

    await expect(preferencesPage.licenseTrigger).toHaveText('No license');

    const preferences = await owner.preferences.getPreferences();
    expect(preferences['license.default'] ?? null).toBeNull();

    expect(pageErrors).toEqual([]);
    expect(consoleErrors).toEqual([]);
  });

  test('saving an unrelated field keeps the license cleared', async ({
    settingsApi,
  }) => {
    await settingsApi.set('image', { enableLicensing: true });

    await preferencesPage.goto();
    await expect(preferencesPage.heading).toBeVisible();

    await preferencesPage.selectOption(
      preferencesPage.landingPageTrigger,
      'Upload',
    );

    await saveAndReload(page, preferencesPage);

    await expect(preferencesPage.landingPageTrigger).toHaveText('Upload');
    await expect(preferencesPage.licenseTrigger).toHaveText('No license');

    const preferences = await owner.preferences.getPreferences();
    expect(preferences['license.default'] ?? null).toBeNull();
  });

  test('picking a real license again works', async ({ settingsApi }) => {
    await settingsApi.set('image', { enableLicensing: true });

    await preferencesPage.goto();
    await expect(preferencesPage.heading).toBeVisible();

    const title = await preferencesPage.pickAnyLicense();
    expect(title).not.toBe('No license');

    await saveAndReload(page, preferencesPage);

    await expect(preferencesPage.licenseTrigger).toHaveText(title);

    const preferences = await owner.preferences.getPreferences();
    expect(preferences['license.default']).toBeTruthy();
  });
});
