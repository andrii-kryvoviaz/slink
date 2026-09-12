import type { BrowserContext, Page } from '@playwright/test';

import { expect, test } from '../fixtures/auth.fixture';
import { unique } from '../helpers/accounts';
import { provisionUser } from '../helpers/provisioning';
import { signInContext } from '../helpers/session';
import { LayoutControls } from '../pages/LayoutControls';
import { PreferencesPage } from '../pages/PreferencesPage';

test.describe('Auto-publish preference', { tag: '@serial' }, () => {
  let context: BrowserContext;
  let page: Page;
  let preferencesPage: PreferencesPage;
  let layoutControls: LayoutControls;

  test.beforeAll(async ({ browser }) => {
    const account = unique('apub');
    const api = await provisionUser(account);

    await api.preferences.updatePreferences({
      'image.externalUploadAutoPublish': true,
    });

    context = await signInContext(browser, account);
    page = await context.newPage();
    preferencesPage = new PreferencesPage(page);
    layoutControls = new LayoutControls(page);
  });

  test.afterAll(async () => {
    await context?.close();
  });

  test('stays visible and enabled when only public images are allowed', async ({
    settingsApi,
  }) => {
    await settingsApi.set('image', { allowOnlyPublicImages: true });

    await preferencesPage.goto();
    await expect(preferencesPage.heading).toBeVisible();

    await expect(preferencesPage.autoPublishSwitch).toBeVisible();
    await expect(preferencesPage.autoPublishSwitch).toHaveAttribute(
      'aria-checked',
      'true',
    );

    await preferencesPage.selectTheme('nord');
    await preferencesPage.save();

    await expect.poll(() => layoutControls.readTheme()).toBe('nord');

    await page.reload();
    await expect(preferencesPage.heading).toBeVisible();

    await expect(preferencesPage.autoPublishSwitch).toHaveAttribute(
      'aria-checked',
      'true',
    );
  });
});

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

test.describe('Preferences form forwarding', { tag: '@serial' }, () => {
  let context: BrowserContext;
  let page: Page;
  let preferencesPage: PreferencesPage;

  test.beforeAll(async ({ browser }) => {
    const account = unique('pfwd');
    const api = await provisionUser(account);

    await api.preferences.updatePreferences({
      'image.externalUploadAutoPublish': true,
    });

    context = await signInContext(browser, account);
    page = await context.newPage();
    preferencesPage = new PreferencesPage(page);
  });

  test.afterAll(async () => {
    await context?.close();
  });

  test('turning auto-publish off persists as off', async ({ settingsApi }) => {
    await settingsApi.set('image', {
      enableLicensing: true,
      allowOnlyPublicImages: false,
    });

    await preferencesPage.goto();
    await expect(preferencesPage.heading).toBeVisible();

    await expect(preferencesPage.autoPublishSwitch).toHaveAttribute(
      'aria-checked',
      'true',
    );

    await preferencesPage.turnSwitchOff(preferencesPage.autoPublishSwitch);

    await expect(preferencesPage.autoPublishSwitch).toHaveAttribute(
      'aria-checked',
      'false',
    );
    await expect(
      page.locator('input[name="image.externalUploadAutoPublish"]'),
    ).toHaveValue('false');

    await saveAndReload(page, preferencesPage);

    await expect(preferencesPage.autoPublishSwitch).toHaveAttribute(
      'aria-checked',
      'false',
    );
    await expect(preferencesPage.exifTrigger).toHaveText('Use server default');
    await expect(preferencesPage.licenseTrigger).toHaveText('No license');
  });

  test('only-public mode leaves the stored default visibility unchanged', async ({
    settingsApi,
  }) => {
    await settingsApi.set('image', { allowOnlyPublicImages: false });

    await preferencesPage.goto();
    await expect(preferencesPage.heading).toBeVisible();

    await preferencesPage.selectOption(
      preferencesPage.visibilityTrigger,
      'Public',
    );
    await saveAndReload(page, preferencesPage);
    await expect(preferencesPage.visibilityTrigger).toHaveText('Public');

    await settingsApi.set('image', { allowOnlyPublicImages: true });
    await preferencesPage.goto();
    await expect(preferencesPage.heading).toBeVisible();

    await expect(preferencesPage.visibilityTrigger).toHaveCount(0);
    await expect(
      page.locator('input[name="image.defaultVisibility"]'),
    ).toHaveCount(0);
    await expect(preferencesPage.autoPublishSwitch).toBeAttached();

    await preferencesPage.selectOption(
      preferencesPage.landingPageTrigger,
      'Upload',
    );
    await saveAndReload(page, preferencesPage);

    await settingsApi.set('image', { allowOnlyPublicImages: false });
    await page.reload();
    await expect(preferencesPage.heading).toBeVisible();

    await expect(preferencesPage.visibilityTrigger).toHaveText('Public');
    await expect(preferencesPage.landingPageTrigger).toHaveText('Upload');
  });

  test('licensing disabled leaves the stored default license unchanged', async ({
    settingsApi,
  }) => {
    await settingsApi.set('image', { enableLicensing: true });

    await preferencesPage.goto();
    await expect(preferencesPage.heading).toBeVisible();

    const licenseTitle = await preferencesPage.pickAnyLicense();
    await saveAndReload(page, preferencesPage);
    await expect(preferencesPage.licenseTrigger).toHaveText(licenseTitle);

    await settingsApi.set('image', { enableLicensing: false });
    await page.reload();
    await expect(preferencesPage.heading).toBeVisible();

    await expect(preferencesPage.licensingSection).toHaveCount(0);

    await preferencesPage.selectTheme('nord');
    await saveAndReload(page, preferencesPage);

    await settingsApi.set('image', { enableLicensing: true });
    await page.reload();
    await expect(preferencesPage.heading).toBeVisible();

    await expect(preferencesPage.licenseTrigger).toHaveText(licenseTitle);
  });
});
