import type { Page } from '@playwright/test';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

import { type SettingsApi, expect, test } from '../fixtures/auth.fixture';
import type { ApiClient } from '../helpers/api';
import { stageDemoMode } from '../helpers/demoMode';
import {
  createSsoProvider,
  deleteSsoProviders,
  removeEverySsoProvider,
} from '../helpers/ssoProviders';
import type { LoginPage } from '../pages/LoginPage';

const PREFIX = 'e2e-login-states';
const SSO_UNAVAILABLE =
  'SSO provider is currently unavailable. Please try again later.';
const UNREACHABLE_DISCOVERY_URL = 'http://127.0.0.1:9';
const DEMO_INSTANCE_NOTICE =
  'Demo instance - uploads and state changes are disabled';
const LOCALES_DIR = path.resolve(
  path.dirname(fileURLToPath(import.meta.url)),
  '../../src/locales',
);
const TRANSLATED_LOCALES = ['de', 'es', 'fr', 'it', 'ja', 'pl', 'uk', 'zh'];

type CatalogEntry = { msgid: string; msgstr: string };

function readCatalog(locale: string): CatalogEntry[] {
  const catalog = fs.readFileSync(
    path.join(LOCALES_DIR, `${locale}.po`),
    'utf8',
  );
  const entries = catalog.matchAll(/^msgid (".*")\nmsgstr (".*")$/gm);

  return Array.from(entries, ([, msgid, msgstr]) => ({
    msgid: JSON.parse(msgid),
    msgstr: JSON.parse(msgstr),
  }));
}

function noticeEntries(locale: string): CatalogEntry[] {
  return readCatalog(locale).filter(({ msgid }) =>
    /no sign-in method/i.test(msgid),
  );
}

async function switchLocalAuthOffAndLoseTheEnabledProvider(
  api: ApiClient,
  settingsApi: SettingsApi,
) {
  const provider = await createSsoProvider(api, PREFIX);

  await settingsApi.set('user', { localAuthEnabled: false });
  await api.oauth.deleteProvider(provider.id);
}

async function expectNoLocalLogin(loginPage: LoginPage) {
  await expect(loginPage.usernameInput).toHaveCount(0);
  await expect(loginPage.passwordInput).toHaveCount(0);
  await expect(loginPage.submitButton).toHaveCount(0);
}

async function expectLocalLogin(loginPage: LoginPage) {
  await expect(loginPage.usernameInput).toBeVisible();
  await expect(loginPage.passwordInput).toBeVisible();
  await expect(loginPage.submitButton).toBeVisible();
}

async function gotoHydratedDemoLogin(page: Page, loginPage: LoginPage) {
  await loginPage.goto();
  await expect(page.getByText(DEMO_INSTANCE_NOTICE)).toBeVisible();
}

test.describe(
  'Login page states',
  { tag: ['@serial', '@anonymous', '@sc-1187'] },
  () => {
    test.beforeEach(async ({ api, settingsApi }) => {
      await removeEverySsoProvider(api);
      await settingsApi.set('user', { autoRedirectProviderId: null });
    });

    test.afterEach(({ api }) => deleteSsoProviders(api, PREFIX));

    test('demo mode with local auth off and no provider shows the notice, not the banner', async ({
      page,
      api,
      settingsApi,
      testUser,
      loginPage,
    }) => {
      await switchLocalAuthOffAndLoseTheEnabledProvider(api, settingsApi);
      await stageDemoMode(page, testUser);

      await gotoHydratedDemoLogin(page, loginPage);

      await expect(loginPage.demoBanner).toHaveCount(0);
      await expect(loginPage.demoGetInButton).toHaveCount(0);
      await expect(loginPage.noSignInMethodNotice).toBeVisible();
    });

    test('demo mode with local auth off and a provider shows the provider, not the banner', async ({
      page,
      api,
      settingsApi,
      testUser,
      loginPage,
    }) => {
      const provider = await createSsoProvider(api, PREFIX);
      await settingsApi.set('user', { localAuthEnabled: false });
      await stageDemoMode(page, testUser);

      await gotoHydratedDemoLogin(page, loginPage);

      await expect(loginPage.ssoProviderLink(provider.slug)).toBeVisible();
      await expect(loginPage.demoBanner).toHaveCount(0);
      await expect(loginPage.demoGetInButton).toHaveCount(0);
    });

    test('demo mode with local auth on shows the banner and Get In signs in', async ({
      page,
      settingsApi,
      testUser,
      loginPage,
    }) => {
      await settingsApi.set('user', { localAuthEnabled: true });
      await stageDemoMode(page, testUser);

      await gotoHydratedDemoLogin(page, loginPage);

      await expect(loginPage.demoBanner).toBeVisible();

      await loginPage.demoGetInButton.click();

      await page.waitForURL(
        (url) => !url.pathname.startsWith('/profile/login'),
        { timeout: 30_000 },
      );
      const cookies = await page.context().cookies();
      expect(cookies.map(({ name }) => name)).toContain('sessionId');
    });

    test('local auth off and no provider shows the notice instead of an empty card', async ({
      page,
      api,
      settingsApi,
      loginPage,
    }) => {
      await switchLocalAuthOffAndLoseTheEnabledProvider(api, settingsApi);

      await page.goto('/profile/login');

      await expect(loginPage.heading).toBeVisible();
      await expect(loginPage.noSignInMethodNotice).toBeVisible();
      await expectNoLocalLogin(loginPage);
    });

    test('local auth off and only a disabled provider shows the notice', async ({
      page,
      api,
      settingsApi,
      loginPage,
    }) => {
      const disabled = await createSsoProvider(api, PREFIX, {
        enabled: false,
      });
      await switchLocalAuthOffAndLoseTheEnabledProvider(api, settingsApi);

      await page.goto('/profile/login');

      await expect(loginPage.noSignInMethodNotice).toBeVisible();
      await expect(loginPage.ssoProviderLink(disabled.slug)).toHaveCount(0);
    });

    test('local auth off and an enabled provider shows the provider and no notice', async ({
      page,
      api,
      settingsApi,
      loginPage,
    }) => {
      const provider = await createSsoProvider(api, PREFIX);
      await settingsApi.set('user', { localAuthEnabled: false });

      await page.goto('/profile/login');

      await expect(loginPage.ssoProviderLink(provider.slug)).toBeVisible();
      await expect(loginPage.noSignInMethodNotice).toHaveCount(0);
    });

    test('local auth on and no provider shows the form and no notice', async ({
      page,
      settingsApi,
      loginPage,
    }) => {
      await settingsApi.set('user', { localAuthEnabled: true });

      await page.goto('/profile/login');

      await expectLocalLogin(loginPage);
      await expect(loginPage.noSignInMethodNotice).toHaveCount(0);
    });

    test('local auth on and a provider shows the form, the provider and no notice', async ({
      page,
      api,
      settingsApi,
      loginPage,
    }) => {
      const provider = await createSsoProvider(api, PREFIX);
      await settingsApi.set('user', { localAuthEnabled: true });

      await page.goto('/profile/login');

      await expectLocalLogin(loginPage);
      await expect(loginPage.ssoProviderLink(provider.slug)).toBeVisible();
      await expect(loginPage.noSignInMethodNotice).toHaveCount(0);
    });

    test('an SSO error with no enabled provider left shows both notices', async ({
      page,
      api,
      settingsApi,
      loginPage,
    }) => {
      const provider = await createSsoProvider(api, PREFIX, {
        discoveryUrl: UNREACHABLE_DISCOVERY_URL,
      });
      await settingsApi.set('user', { localAuthEnabled: false });

      await page.goto('/profile/login');
      await api.oauth.updateProvider(provider.id, { enabled: false });
      await loginPage.ssoProviderLink(provider.slug).click();

      await expect(page.getByText(SSO_UNAVAILABLE)).toBeVisible({
        timeout: 15_000,
      });
      await expect(loginPage.noSignInMethodNotice).toBeVisible();
    });

    test('the English catalog has one entry for the notice', () => {
      expect(noticeEntries('en')).toHaveLength(1);
    });

    test.describe('in every translated locale', () => {
      test.afterEach(({ localeHelper }) => localeHelper.reset());

      for (const locale of TRANSLATED_LOCALES) {
        test(`the ${locale} catalog translates the notice and the page renders it`, async ({
          page,
          api,
          settingsApi,
          localeHelper,
        }) => {
          const entries = noticeEntries(locale);

          expect(entries).toHaveLength(1);

          const [{ msgid, msgstr }] = entries;

          expect(msgstr).not.toBe('');
          expect(msgstr).not.toBe(msgid);

          await switchLocalAuthOffAndLoseTheEnabledProvider(api, settingsApi);
          await localeHelper.set(locale);

          await page.goto('/profile/login');

          await expect(page.getByText(msgstr)).toBeVisible();
        });
      }
    });
  },
);
