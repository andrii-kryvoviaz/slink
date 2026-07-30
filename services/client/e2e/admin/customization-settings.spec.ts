import { type Page } from '@playwright/test';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

import { expect, test } from '../fixtures/auth.fixture';

const API_URL = process.env.E2E_API_URL ?? 'http://localhost:8180';

const PIXEL_PNG = path.resolve(
  path.dirname(fileURLToPath(import.meta.url)),
  '../fixtures/pixel.png',
);

const DEFAULT_CUSTOMIZATION = {
  siteName: 'Slink',
  siteDescription: 'Fast and secure image sharing service',
  logoUrl: '',
};

const BRANDING_LOGO_URL = /\/api\/branding\/logo\?v=[0-9a-f]{8}$/;

function siteNameInput(page: Page) {
  return page.locator('input[name="customizationSiteName"]');
}

function logoUrlInput(page: Page) {
  return page.locator('input[name="customizationLogoUrl"]');
}

function customizationForm(page: Page) {
  return page.locator('form').filter({ has: siteNameInput(page) });
}

function siteNameItem(page: Page) {
  return page
    .locator('div.relative.flex-col', { has: siteNameInput(page) })
    .last();
}

async function gotoCustomization(page: Page) {
  await page.goto('/admin/settings/customization');
  await expect(siteNameInput(page)).toBeVisible();
}

async function fillSiteName(page: Page, value: string) {
  const resetTrigger = siteNameItem(page).getByLabel('Reset to default value');

  await expect(async () => {
    await siteNameInput(page).fill(value);
    await expect(resetTrigger).toHaveCSS('pointer-events', 'auto', {
      timeout: 1000,
    });
  }).toPass({ timeout: 15000 });
}

async function saveCustomization(page: Page) {
  const saved = page.waitForResponse(
    (response) =>
      response.url().endsWith('/api/settings') &&
      response.request().method() === 'POST',
  );
  const reloaded = page.waitForEvent('load');

  await customizationForm(page)
    .getByRole('button', { name: 'Save Changes' })
    .click();

  expect((await saved).ok()).toBe(true);
  await reloaded;
}

test.describe('Admin customization settings', { tag: '@serial' }, () => {
  test.afterEach(async ({ api }) => {
    await api.settings.updateSettings('customization', DEFAULT_CUSTOMIZATION);
  });

  test('round-trips a custom site name to admin chrome and the public login page', async ({
    page,
    browser,
  }) => {
    const customName = 'Acme Screens';

    await gotoCustomization(page);
    await fillSiteName(page, customName);
    await saveCustomization(page);

    await expect(
      page.getByRole('link', { name: customName, exact: true }).first(),
    ).toBeVisible();

    const anonymousContext = await browser.newContext({
      storageState: undefined,
    });
    const anonymousPage = await anonymousContext.newPage();
    await anonymousPage.goto('/profile/login');
    await expect(
      anonymousPage.getByText(`Sign in to continue to ${customName}`),
    ).toBeVisible();
    await anonymousContext.close();
  });

  test('rejects an oversized site name with a customization.siteName violation', async ({
    api,
  }) => {
    const response = await fetch(`${API_URL}/api/settings`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${api.token}`,
      },
      body: JSON.stringify({
        category: 'customization',
        settings: {
          ...DEFAULT_CUSTOMIZATION,
          siteName: 'a'.repeat(65),
        },
      }),
    });

    expect(response.status).toBe(400);

    const payload = await response.json();
    const violation = payload?.error?.violations?.find(
      (item: { property?: string }) =>
        item.property === 'customization.siteName',
    );
    expect(violation).toBeTruthy();
  });

  test('uploads a logo file and serves it from the branding endpoint', async ({
    page,
  }) => {
    await gotoCustomization(page);

    const fileInput = customizationForm(page).locator('input[type="file"]');

    await expect(async () => {
      const uploaded = page.waitForResponse(
        (response) =>
          response.url().includes('/api/settings/customization/logo') &&
          response.request().method() === 'POST',
        { timeout: 2000 },
      );
      await fileInput.setInputFiles([]);
      await fileInput.setInputFiles(PIXEL_PNG);
      expect((await uploaded).ok()).toBe(true);
    }).toPass({ timeout: 15000 });

    await expect(logoUrlInput(page)).toHaveValue(BRANDING_LOGO_URL);

    await saveCustomization(page);

    const brandLogo = page.getByAltText('Slink').first();
    await expect(brandLogo).toHaveAttribute('src', BRANDING_LOGO_URL);

    const src = await brandLogo.getAttribute('src');
    const logoResponse = await page.request.get(String(src));
    expect(logoResponse.status()).toBe(200);
  });

  test('falls back to the default favicon when the logo url is unreachable', async ({
    page,
    api,
  }) => {
    await api.settings.updateSettings('customization', {
      ...DEFAULT_CUSTOMIZATION,
      logoUrl: 'https://localhost:1/logo.png',
    });

    await gotoCustomization(page);

    const brandLogo = page.getByAltText('Slink').first();
    await expect(brandLogo).toHaveAttribute('src', '/favicon.png');
  });

  test('forbids logo upload for a non-admin user', async ({ actor }) => {
    const nonOwner = await actor('nonOwner');

    const form = new FormData();
    form.append(
      'file',
      new Blob([fs.readFileSync(PIXEL_PNG)], { type: 'image/png' }),
      'pixel.png',
    );

    const response = await fetch(`${API_URL}/api/settings/customization/logo`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${nonOwner.token}` },
      body: form,
    });

    expect(response.status).toBe(403);
  });

  test('resets a changed site name back to its default value', async ({
    page,
  }) => {
    await gotoCustomization(page);
    await fillSiteName(page, 'Temporary Name');

    const row = siteNameItem(page);
    const resetTrigger = row.getByLabel('Reset to default value');
    const confirmButton = row.getByRole('button', { name: 'Confirm' });

    await expect(async () => {
      await resetTrigger.click();
      await expect(confirmButton).toBeVisible({ timeout: 1000 });
    }).toPass({ timeout: 15000 });
    await confirmButton.click();

    await expect(siteNameInput(page)).toHaveValue(
      DEFAULT_CUSTOMIZATION.siteName,
    );
  });
});
