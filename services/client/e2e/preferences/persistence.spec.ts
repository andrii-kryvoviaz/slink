import type { BrowserContext, Page } from '@playwright/test';

import { expect, test } from '../fixtures/auth.fixture';
import { unique } from '../helpers/accounts';
import { provisionUser } from '../helpers/provisioning';
import { signInContext } from '../helpers/session';
import { LayoutControls } from '../pages/LayoutControls';
import { PreferencesPage } from '../pages/PreferencesPage';

test.describe('Preferences persistence', { tag: '@serial' }, () => {
  let context: BrowserContext;
  let page: Page;
  let preferencesPage: PreferencesPage;
  let layoutControls: LayoutControls;

  test.beforeAll(async ({ browser }) => {
    const account = unique('prefs');
    await provisionUser(account);

    context = await signInContext(browser, account);
    page = await context.newPage();
    preferencesPage = new PreferencesPage(page);
    layoutControls = new LayoutControls(page);
  });

  test.afterAll(async () => {
    await context?.close();
  });

  test('saves every field through the UI and leaves untouched fields alone', async ({
    settingsApi,
  }) => {
    await settingsApi.set('image', {
      enableLicensing: true,
      allowOnlyPublicImages: false,
    });

    await preferencesPage.goto();
    await expect(preferencesPage.heading).toBeVisible();

    await expect(preferencesPage.navigationSection).toBeVisible();
    await expect(preferencesPage.imageUploadsSection).toBeVisible();
    await expect(preferencesPage.licensingSection).toBeVisible();

    await expect(preferencesPage.landingPageTrigger).toHaveCount(1);
    await expect(preferencesPage.visibilityTrigger).toHaveCount(1);
    await expect(preferencesPage.exifTrigger).toHaveCount(1);
    await expect(preferencesPage.licenseTrigger).toHaveCount(1);
    await expect(preferencesPage.syncLicenseSwitch).toHaveCount(1);

    await expect(preferencesPage.landingPageTrigger).toHaveText('Explore');
    await expect(preferencesPage.visibilityTrigger).toHaveText('Private');
    await expect(preferencesPage.exifTrigger).toHaveText('Use server default');
    await expect(preferencesPage.autoPublishSwitch).toHaveAttribute(
      'aria-checked',
      'false',
    );
    await expect(preferencesPage.licenseTrigger).toHaveText(
      'All Rights Reserved',
    );
    await expect(preferencesPage.themeTrigger).toHaveText('Default');

    await preferencesPage.selectOption(
      preferencesPage.landingPageTrigger,
      'Upload',
    );
    await preferencesPage.selectOption(
      preferencesPage.visibilityTrigger,
      'Public',
    );
    await preferencesPage.selectOption(
      preferencesPage.exifTrigger,
      'Always strip',
    );
    const licenseTitle = await preferencesPage.pickAnyLicense();
    await preferencesPage.selectTheme('nord');

    await preferencesPage.setSwitch(preferencesPage.autoPublishSwitch, true);
    await preferencesPage.setSwitch(preferencesPage.syncLicenseSwitch, true);

    await expect(preferencesPage.autoPublishSwitch).toHaveAttribute(
      'aria-checked',
      'true',
    );
    await expect(preferencesPage.syncLicenseSwitch).toHaveAttribute(
      'aria-checked',
      'true',
    );

    await preferencesPage.saveAndReload();

    await expect(preferencesPage.landingPageTrigger).toHaveText('Upload');
    await expect(preferencesPage.visibilityTrigger).toHaveText('Public');
    await expect(preferencesPage.exifTrigger).toHaveText('Always strip');
    await expect(preferencesPage.autoPublishSwitch).toHaveAttribute(
      'aria-checked',
      'true',
    );
    await expect(preferencesPage.licenseTrigger).toHaveText(licenseTitle);
    await expect(preferencesPage.themeTrigger).toHaveText('Nord');
    await expect.poll(() => layoutControls.readTheme()).toBe('nord');
    await expect(preferencesPage.syncLicenseSwitch).toHaveAttribute(
      'aria-checked',
      'false',
    );

    await preferencesPage.selectOption(
      preferencesPage.landingPageTrigger,
      'Explore',
    );

    await preferencesPage.saveAndReload();

    await expect(preferencesPage.landingPageTrigger).toHaveText('Explore');
    await expect(preferencesPage.visibilityTrigger).toHaveText('Public');
    await expect(preferencesPage.exifTrigger).toHaveText('Always strip');
    await expect(preferencesPage.autoPublishSwitch).toHaveAttribute(
      'aria-checked',
      'true',
    );
    await expect(preferencesPage.licenseTrigger).toHaveText(licenseTitle);
    await expect(preferencesPage.themeTrigger).toHaveText('Nord');
    await expect(preferencesPage.syncLicenseSwitch).toHaveAttribute(
      'aria-checked',
      'false',
    );

    await preferencesPage.selectOption(
      preferencesPage.landingPageTrigger,
      'Upload',
    );
    await preferencesPage.save();
    await expect(await preferencesPage.waitForToast()).toContainText(
      'Preferences updated successfully',
    );
    await expect(preferencesPage.landingPageTrigger).toHaveText('Upload');

    await preferencesPage.selectOption(
      preferencesPage.exifTrigger,
      'Always keep',
    );
    await preferencesPage.saveAndReload();

    await expect(preferencesPage.landingPageTrigger).toHaveText('Upload');
    await expect(preferencesPage.exifTrigger).toHaveText('Always keep');
  });
});
