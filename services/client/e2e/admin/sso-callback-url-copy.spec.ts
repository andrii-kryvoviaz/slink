import type { Page } from '@playwright/test';

import { expect, test } from '../fixtures/auth.fixture';
import type { SsoSettingsPage } from '../pages/SsoSettingsPage';

const expectTitles = async (
  ssoSettingsPage: SsoSettingsPage,
  textTitle: string,
  iconTitle: string,
) => {
  await expect(ssoSettingsPage.callbackUrlButton).toHaveAttribute(
    'title',
    textTitle,
  );
  await expect(ssoSettingsPage.callbackUrlCopyButton).toHaveAttribute(
    'title',
    iconTitle,
  );
};

const expectCallbackUrlCopied = async (
  page: Page,
  ssoSettingsPage: SsoSettingsPage,
  baseURL: string | undefined,
) => {
  expect(await page.evaluate(() => navigator.clipboard.readText())).toBe(
    new URL('/profile/sso/callback', baseURL).href,
  );
  await expectTitles(ssoSettingsPage, 'Copied!', 'Copied!');
};

test.use({
  permissions: ['clipboard-read', 'clipboard-write'],
});

test.describe('Admin SSO callback URL copy', () => {
  test.beforeEach(async ({ ssoSettingsPage }) => {
    await ssoSettingsPage.gotoWithPausedClock(() => ssoSettingsPage.gotoList());
    await expect(ssoSettingsPage.callbackUrlChip).toContainText('Callback URL');
  });

  test('copying the callback URL text marks both buttons copied until the two second reset', async ({
    page,
    baseURL,
    ssoSettingsPage,
  }) => {
    await ssoSettingsPage.clickUntilOnPausedClock(
      ssoSettingsPage.callbackUrlButton,
      page.getByRole('button', { name: 'Copied!', exact: true }),
    );

    await expectCallbackUrlCopied(page, ssoSettingsPage, baseURL);

    await page.clock.runFor(1600);
    await expectTitles(ssoSettingsPage, 'Copied!', 'Copied!');

    await page.clock.runFor(400);
    await expectTitles(ssoSettingsPage, 'Click to copy', 'Copy to clipboard');
  });

  test('copying from the icon button writes the callback URL and marks both buttons copied', async ({
    page,
    baseURL,
    ssoSettingsPage,
  }) => {
    await expectTitles(ssoSettingsPage, 'Click to copy', 'Copy to clipboard');

    await ssoSettingsPage.clickUntilOnPausedClock(
      ssoSettingsPage.callbackUrlCopyButton,
      page.getByRole('button', { name: 'Copied!', exact: true }),
    );

    await expectCallbackUrlCopied(page, ssoSettingsPage, baseURL);
  });
});

test.describe('Admin SSO provider form callback URL', () => {
  test('shows the callback guidance under the configuration heading and copies the URL', async ({
    page,
    baseURL,
    ssoSettingsPage,
  }) => {
    await ssoSettingsPage.gotoNew();
    await ssoSettingsPage.selectCustomProvider();

    const heading = page.getByRole('heading', { name: 'Configuration' });

    await expect(ssoSettingsPage.callbackUrlGuidance).toBeVisible();
    await expect(ssoSettingsPage.callbackUrlChip).toContainText('Callback URL');
    await expect(ssoSettingsPage.callbackUrlChip).toContainText(
      new URL('/profile/sso/callback', baseURL).href,
    );

    const headingBox = await heading.boundingBox();
    const guidanceBox = await ssoSettingsPage.callbackUrlGuidance.boundingBox();
    const chipBox = await ssoSettingsPage.callbackUrlChip.boundingBox();

    expect(guidanceBox!.y).toBeGreaterThan(headingBox!.y);
    expect(chipBox!.y).toBeGreaterThan(guidanceBox!.y);

    await ssoSettingsPage.clickUntil(
      ssoSettingsPage.callbackUrlCopyButton,
      page.getByRole('button', { name: 'Copied!', exact: true }),
    );

    await expectCallbackUrlCopied(page, ssoSettingsPage, baseURL);
  });
});
