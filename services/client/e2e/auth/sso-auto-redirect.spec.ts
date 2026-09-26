import type { APIResponse, Page } from '@playwright/test';

import { expect, test } from '../fixtures/auth.fixture';
import { unique } from '../helpers/accounts';
import { provisionUser } from '../helpers/provisioning';
import { signInContext } from '../helpers/session';
import {
  createSsoProvider,
  deleteSsoProviders,
  ssoProviderPayload,
  storedAutoRedirectProviderId,
  uniqueSsoSlug,
} from '../helpers/ssoProviders';
import type { LoginPage } from '../pages/LoginPage';

const PREFIX = 'e2e-login-redirect';
const SSO_LOGIN_PATH = '/profile/sso/login/';
const SSO_UNAVAILABLE =
  'SSO provider is currently unavailable. Please try again later.';
const LOGOUT_PATH = '/profile/logout';

type Provider = { id: string; slug: string; name: string };

const settingRows: Array<{
  name: 'on' | 'off';
  autoRedirectProviderId: (provider: Provider) => string | null;
}> = [
  { name: 'on', autoRedirectProviderId: (provider) => provider.id },
  { name: 'off', autoRedirectProviderId: () => null },
];

function probe(page: Page, path: string) {
  return page.request.get(path, { maxRedirects: 0 });
}

function locationPath(response: APIResponse, baseURL: string | undefined) {
  return new URL(response.headers()['location'], baseURL).pathname;
}

function recordSsoNavigations(page: Page) {
  const hits: string[] = [];

  page.on('request', (request) => {
    const { pathname } = new URL(request.url());

    if (request.isNavigationRequest() && pathname.startsWith(SSO_LOGIN_PATH)) {
      hits.push(pathname);
    }
  });

  return hits;
}

function recordNavigations(page: Page) {
  const navigations: string[] = [];

  page.on('request', (request) => {
    if (request.isNavigationRequest()) {
      const { pathname } = new URL(request.url());
      navigations.push(`${request.method()} ${pathname}`);
    }
  });

  return navigations;
}

function loginPageContent(page: Page) {
  return page.locator('[data-slot="scroll-area-viewport"]');
}

function providerLink(page: Page, slug: string) {
  return loginPageContent(page).locator(`a[href="${SSO_LOGIN_PATH}${slug}"]`);
}

function anySsoLink(page: Page) {
  return loginPageContent(page).locator(`a[href^="${SSO_LOGIN_PATH}"]`);
}

function logoutHeading(page: Page) {
  return page.getByRole('heading', { name: 'You have been logged out' });
}

function signUpLink(page: Page) {
  return page.getByRole('link', { name: 'Sign Up' });
}

function isBareLoginUrl(url: URL) {
  return url.pathname === '/profile/login' && url.search === '';
}

async function expectFullLoginPage(loginPage: LoginPage) {
  await expect(loginPage.usernameInput).toBeVisible();
  await expect(loginPage.passwordInput).toBeVisible();
  await expect(loginPage.submitButton).toBeVisible();
}

async function signIn(
  page: Page,
  loginPage: LoginPage,
  account: { username: string; password: string },
) {
  await loginPage.login(account.username, account.password);
  await page.waitForURL((url) => !url.pathname.startsWith('/profile/login'), {
    timeout: 30000,
  });
}

async function logOut(page: Page) {
  await page.goto('/');

  await page.evaluate(() => {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/profile/logout';
    document.body.appendChild(form);
    form.requestSubmit();
  });
}

async function gatewayUrl(page: Page): Promise<string> {
  const response = await page.request.get('/profile/login/__data.json');
  const { nodes } = await response.json();
  const [rootLayoutNode] = nodes;
  const data = rootLayoutNode.data;

  return data[data[0].gatewayUrl];
}

function expectedGatewayPath(row: { name: string }, chosen: Provider) {
  return row.name === 'on'
    ? `${SSO_LOGIN_PATH}${chosen.slug}`
    : '/profile/login';
}

async function routeSsoStart(page: Page): Promise<string[]> {
  const hits: string[] = [];

  await page.route('**/profile/sso/login/**', async (route) => {
    const pathname = new URL(route.request().url()).pathname.replace(
      /\/__data\.json$/,
      '',
    );
    hits.push(pathname);
    await route.abort();
  });

  return hits;
}

function recordLoginRequests(page: Page): string[] {
  const hits: string[] = [];

  page.on('request', (request) => {
    const { pathname } = new URL(request.url());

    if (
      pathname === '/profile/login' ||
      pathname === '/profile/login/__data.json'
    ) {
      hits.push(pathname);
    }
  });

  return hits;
}

function sessionLinks(page: Page, name: string) {
  return page.getByRole('link', { name, exact: true });
}

function bareLoginLink(page: Page) {
  return page.locator('a[href="/profile/login"]');
}

test.describe('Login gateway', { tag: ['@serial', '@anonymous'] }, () => {
  test.afterEach(({ api }) => deleteSsoProviders(api, PREFIX));

  for (const row of settingRows) {
    test(`renders the normal full login page and never redirects (${row.name})`, async ({
      page,
      api,
      settingsApi,
      loginPage,
    }) => {
      const other = await createSsoProvider(api, PREFIX);
      const chosen = await createSsoProvider(api, PREFIX);
      await settingsApi.set('user', {
        allowRegistration: true,
        autoRedirectProviderId: row.autoRedirectProviderId(chosen),
      });

      const response = await probe(page, '/profile/login');

      expect(response.status()).toBe(200);
      expect(response.headers()['location']).toBeUndefined();

      const ssoHits = recordSsoNavigations(page);

      await page.goto('/profile/login');

      await expect(page).toHaveURL(isBareLoginUrl);
      await expect(loginPage.heading).toBeVisible();
      await expectFullLoginPage(loginPage);
      await expect(providerLink(page, chosen.slug)).toBeVisible();
      await expect(providerLink(page, other.slug)).toBeVisible();
      await expect(signUpLink(page)).toBeVisible();
      expect(ssoHits).toEqual([]);
    });
  }

  test('gatewayUrl follows the resolved provider', async ({
    page,
    api,
    settingsApi,
    loginPage,
    baseURL,
  }) => {
    const other = await createSsoProvider(api, PREFIX);
    const chosen = await createSsoProvider(api, PREFIX);

    const cases: Array<{
      name: string;
      arrange: () => Promise<void>;
      expected: string;
      resolved: boolean;
      extra?: () => Promise<void>;
    }> = [
      {
        name: 'on',
        arrange: () =>
          settingsApi.set('user', { autoRedirectProviderId: chosen.id }),
        expected: `${SSO_LOGIN_PATH}${chosen.slug}`,
        resolved: true,
      },
      {
        name: 'off',
        arrange: () =>
          settingsApi.set('user', { autoRedirectProviderId: null }),
        expected: '/profile/login',
        resolved: false,
      },
      {
        name: 'disabled',
        arrange: async () => {
          await settingsApi.set('user', {
            autoRedirectProviderId: chosen.id,
          });
          await api.oauth.updateProvider(chosen.id, { enabled: false });
        },
        expected: '/profile/login',
        resolved: false,
        extra: async () => {
          expect(await storedAutoRedirectProviderId(api)).toBe(chosen.id);
        },
      },
      {
        name: 'deleted',
        arrange: async () => {
          await settingsApi.set('user', {
            autoRedirectProviderId: chosen.id,
          });
          await api.oauth.deleteProvider(chosen.id);
        },
        expected: '/profile/login',
        resolved: false,
        extra: async () => {
          expect(await storedAutoRedirectProviderId(api)).toBe(chosen.id);
        },
      },
      {
        name: 'stale',
        arrange: () =>
          settingsApi.set('user', {
            autoRedirectProviderId: crypto.randomUUID(),
          }),
        expected: '/profile/login',
        resolved: false,
      },
    ];

    for (const { name, arrange, expected, resolved, extra } of cases) {
      await arrange();

      expect(await gatewayUrl(page), name).toBe(expected);

      if (extra) {
        await extra();
      }

      expect(locationPath(await probe(page, '/history'), baseURL), name).toBe(
        expected,
      );

      if (resolved) {
        continue;
      }

      const response = await probe(page, '/profile/login');
      expect(response.status(), name).toBe(200);

      await page.goto('/profile/login');
      await expectFullLoginPage(loginPage);
    }
  });

  test('gatewayUrl follows a slug edit of the chosen provider', async ({
    page,
    api,
    settingsApi,
  }) => {
    const provider = await createSsoProvider(api, PREFIX);
    await settingsApi.set('user', { autoRedirectProviderId: provider.id });

    const editedSlug = uniqueSsoSlug(PREFIX);
    await api.oauth.updateProvider(provider.id, ssoProviderPayload(editedSlug));

    const url = await gatewayUrl(page);

    expect(url).toBe(`${SSO_LOGIN_PATH}${editedSlug}`);
    expect(url).not.toContain(provider.slug);
  });

  for (const row of settingRows) {
    test(`logout lands on the normal login page and a reload stays there (${row.name})`, async ({
      page,
      api,
      settingsApi,
      loginPage,
      testUser,
      baseURL,
    }) => {
      await signIn(page, loginPage, testUser);

      const other = await createSsoProvider(api, PREFIX);
      const chosen = await createSsoProvider(api, PREFIX);
      await settingsApi.set('user', {
        allowRegistration: true,
        autoRedirectProviderId: row.autoRedirectProviderId(chosen),
      });

      const navigations = recordNavigations(page);
      const ssoHits = recordSsoNavigations(page);

      await logOut(page);

      await expect(page).toHaveURL(isBareLoginUrl);
      expect(
        navigations.slice(navigations.indexOf('POST /profile/logout')),
      ).toEqual(['POST /profile/logout', 'GET /profile/login']);
      await expectFullLoginPage(loginPage);
      await expect(providerLink(page, chosen.slug)).toBeVisible();
      await expect(providerLink(page, other.slug)).toBeVisible();
      await expect(signUpLink(page)).toBeVisible();
      await expect(logoutHeading(page)).toHaveCount(0);
      expect(ssoHits).toEqual([]);

      const profile = await probe(page, '/profile');
      expect(profile.status()).toBe(302);
      expect(locationPath(profile, baseURL)).not.toBe('/profile');

      navigations.length = 0;
      ssoHits.length = 0;

      await page.reload();

      expect(navigations).toEqual(['GET /profile/login']);
      await expect(page).toHaveURL(isBareLoginUrl);
      await expectFullLoginPage(loginPage);
      expect(ssoHits).toEqual([]);
    });
  }

  test('shows the SSO error on the normal page with no loop', async ({
    page,
    api,
    settingsApi,
    loginPage,
  }) => {
    test.setTimeout(120_000);

    const chosen = await createSsoProvider(api, PREFIX);
    const other = await createSsoProvider(api, PREFIX);
    await settingsApi.set('user', { autoRedirectProviderId: chosen.id });

    await page.goto('/profile/login');

    const ssoHits = recordSsoNavigations(page);

    await providerLink(page, chosen.slug).click();

    await expect(page.getByText(SSO_UNAVAILABLE)).toBeVisible({
      timeout: 90_000,
    });
    expect(new URL(page.url()).pathname).toBe('/profile/login');
    expect(ssoHits).toEqual([`${SSO_LOGIN_PATH}${chosen.slug}`]);
    await expectFullLoginPage(loginPage);
    await expect(providerLink(page, chosen.slug)).toBeVisible();
    await expect(providerLink(page, other.slug)).toBeVisible();

    await page.reload();

    await expect(page.getByText(SSO_UNAVAILABLE)).toHaveCount(0);
    expect(new URL(page.url()).pathname).toBe('/profile/login');
    expect(ssoHits).toHaveLength(1);
    await expectFullLoginPage(loginPage);
  });

  test('the dedicated logout page is gone', async ({
    page,
    api,
    settingsApi,
  }) => {
    const chosen = await createSsoProvider(api, PREFIX);
    await settingsApi.set('user', { autoRedirectProviderId: chosen.id });

    await page.goto(LOGOUT_PATH);

    await expect(logoutHeading(page)).toHaveCount(0);
    await expect(anySsoLink(page)).toHaveCount(0);
  });

  const guardRows: Array<{
    name: 'on' | 'off' | 'disabled';
    autoRedirectProviderId: (provider: Provider) => string | null;
    disableProvider?: boolean;
  }> = [
    { name: 'on', autoRedirectProviderId: (provider) => provider.id },
    { name: 'off', autoRedirectProviderId: () => null },
    {
      name: 'disabled',
      autoRedirectProviderId: (provider) => provider.id,
      disableProvider: true,
    },
  ];

  const GUARDED_ROUTES = [
    '/history',
    '/collections',
    '/preferences',
    '/profile',
    '/profile/awaiting-approval',
    '/admin/dashboard',
  ];

  for (const row of guardRows) {
    test(`guards send an anonymous visitor to the gateway (${row.name})`, async ({
      page,
      api,
      settingsApi,
      baseURL,
    }) => {
      await createSsoProvider(api, PREFIX);
      const chosen = await createSsoProvider(api, PREFIX);
      await settingsApi.set('user', {
        allowRegistration: true,
        approvalRequired: true,
        autoRedirectProviderId: row.autoRedirectProviderId(chosen),
      });

      if (row.disableProvider) {
        await api.oauth.updateProvider(chosen.id, { enabled: false });
      }

      const expected = expectedGatewayPath(row, chosen);

      for (const route of GUARDED_ROUTES) {
        const response = await probe(page, route);

        expect(response.status(), route).toBe(302);
        expect(locationPath(response, baseURL), route).toBe(expected);
      }

      await settingsApi.set('user', { allowRegistration: false });

      const signupResponse = await probe(page, '/profile/signup');

      expect(signupResponse.status()).toBe(302);
      expect(locationPath(signupResponse, baseURL)).toBe(expected);
    });
  }

  for (const row of settingRows) {
    test(`a stale session ends at the gateway (${row.name})`, async ({
      page,
      api,
      settingsApi,
      baseURL,
    }) => {
      await createSsoProvider(api, PREFIX);
      const chosen = await createSsoProvider(api, PREFIX);
      await settingsApi.set('user', {
        autoRedirectProviderId: row.autoRedirectProviderId(chosen),
      });

      await page.context().addCookies([
        { name: 'sessionId', value: crypto.randomUUID(), url: baseURL },
        { name: 'refreshToken', value: crypto.randomUUID(), url: baseURL },
      ]);

      const expected = expectedGatewayPath(row, chosen);

      for (const route of ['/history', '/profile']) {
        const response = await probe(page, route);

        expect(response.status(), route).toBe(302);
        expect(locationPath(response, baseURL), route).toBe(expected);
      }
    });
  }

  for (const row of settingRows) {
    for (const allowGuestUploads of [true, false]) {
      test(`upload page links point at the gateway (${row.name}, guests ${allowGuestUploads})`, async ({
        page,
        api,
        settingsApi,
      }) => {
        await createSsoProvider(api, PREFIX);
        const chosen = await createSsoProvider(api, PREFIX);
        await settingsApi.set('user', {
          autoRedirectProviderId: row.autoRedirectProviderId(chosen),
        });
        await settingsApi.set('access', { allowGuestUploads });

        await page.goto('/upload');

        const expected = expectedGatewayPath(row, chosen);
        const signInLinks = sessionLinks(page, 'Sign In');

        if (allowGuestUploads) {
          await expect(signInLinks).toHaveCount(2);
        } else {
          await expect(signInLinks).toHaveCount(1);
          await expect(sessionLinks(page, 'Get Started')).toHaveCount(1);
          for (const link of await sessionLinks(page, 'Get Started').all()) {
            await expect(link).toHaveAttribute('href', expected);
          }
        }

        for (const link of await signInLinks.all()) {
          await expect(link).toHaveAttribute('href', expected);
        }

        if (row.name === 'on') {
          await expect(bareLoginLink(page)).toHaveCount(0);
        } else {
          await expect(anySsoLink(page)).toHaveCount(0);
        }
      });
    }
  }

  for (const row of settingRows) {
    test(`the signup and awaiting-approval Sign In links point at the gateway (${row.name})`, async ({
      page,
      api,
      settingsApi,
      signupPage,
      awaitingApprovalPage,
    }) => {
      await createSsoProvider(api, PREFIX);
      const chosen = await createSsoProvider(api, PREFIX);
      await settingsApi.set('user', {
        allowRegistration: true,
        approvalRequired: true,
        autoRedirectProviderId: row.autoRedirectProviderId(chosen),
      });

      const expected = expectedGatewayPath(row, chosen);

      await page.goto('/profile/signup');

      const signupBanner = page
        .getByText('Already have an account?')
        .locator('xpath=ancestor::*[3]');
      const signupSignInLink = signupBanner.getByRole('link', {
        name: 'Sign In',
      });

      await expect(signupSignInLink).toHaveAttribute('href', expected);

      if (row.name === 'on') {
        await expect(bareLoginLink(page)).toHaveCount(0);
      } else {
        await expect(anySsoLink(page)).toHaveCount(0);
      }

      const applicant = unique('applicant');

      await signupPage.signup({
        username: applicant.username,
        email: applicant.email,
        password: applicant.password,
        confirm: applicant.password,
      });

      await expect(page).toHaveURL(/\/profile\/awaiting-approval/);
      await expect(awaitingApprovalPage.reviewHeading).toBeVisible();
      await expect(awaitingApprovalPage.signInLink).toHaveAttribute(
        'href',
        expected,
      );

      if (row.name === 'on') {
        await expect(bareLoginLink(page)).toHaveCount(0);
      } else {
        await expect(anySsoLink(page)).toHaveCount(0);
      }
    });
  }
});

test.describe('Login gateway for a signed-in user', { tag: '@serial' }, () => {
  test.afterEach(({ api }) => deleteSsoProviders(api, PREFIX));

  for (const row of settingRows) {
    test(`sends a signed-in user from the login page to the profile (${row.name})`, async ({
      page,
      api,
      settingsApi,
      baseURL,
    }) => {
      const provider = await createSsoProvider(api, PREFIX);
      await settingsApi.set('user', {
        autoRedirectProviderId: row.autoRedirectProviderId(provider),
      });

      const response = await probe(page, '/profile/login');

      expect(response.status()).toBe(302);
      expect(locationPath(response, baseURL)).toBe('/profile');
    });
  }

  test('describes the auto-redirect setting without a local bypass hint', async ({
    api,
    settingsApi,
    ssoSettingsPage,
  }) => {
    await settingsApi.set('user', { autoRedirectProviderId: null });
    await createSsoProvider(api, PREFIX);

    await ssoSettingsPage.gotoList();

    await expect(ssoSettingsPage.autoRedirectRow).toContainText(
      'Skip the login page and send visitors to this provider',
    );
    await expect(ssoSettingsPage.autoRedirectRow).not.toContainText('?local');
    await expect(ssoSettingsPage.autoRedirectRow).not.toContainText(
      '/profile/login',
    );
  });

  for (const row of settingRows) {
    test(`a signed-in member is not sent to the gateway (${row.name})`, async ({
      api,
      settingsApi,
      browser,
      baseURL,
    }) => {
      const provider = await createSsoProvider(api, PREFIX);
      await settingsApi.set('user', {
        autoRedirectProviderId: row.autoRedirectProviderId(provider),
      });

      const member = unique('member');
      await provisionUser(member);
      const context = await signInContext(browser, member);

      try {
        for (const route of ['/admin/dashboard', '/admin/settings/sso']) {
          const firstHop = await context.request.get(route, {
            maxRedirects: 0,
          });

          expect(firstHop.status(), route).toBe(302);
          expect(locationPath(firstHop, baseURL), route).toBe('/profile/login');

          const response = await context.request.get(route);

          expect(new URL(response.url()).pathname, route).toBe('/profile');
        }

        const history = await context.request.get('/history', {
          maxRedirects: 0,
        });

        expect(history.status()).toBe(200);
        expect(history.headers()['location']).toBeUndefined();
      } finally {
        await context.close();
      }
    });
  }

  for (const row of settingRows) {
    test(`a client-side 401 navigates to the gateway (${row.name})`, async ({
      page,
      api,
      settingsApi,
      ssoSettingsPage,
      loginPage,
    }) => {
      test.setTimeout(60_000);

      const other = await createSsoProvider(api, PREFIX);
      const chosen = await createSsoProvider(api, PREFIX);
      await settingsApi.set('user', {
        autoRedirectProviderId: row.autoRedirectProviderId(chosen),
      });

      await ssoSettingsPage.gotoList();
      await expect(ssoSettingsPage.autoRedirectTrigger).toBeVisible();

      const ssoHits = await routeSsoStart(page);
      const loginHits = recordLoginRequests(page);

      await page.context().clearCookies();

      await ssoSettingsPage.selectAutoRedirect(other.name);

      if (row.name === 'on') {
        await expect
          .poll(() => ssoHits)
          .toContain(`${SSO_LOGIN_PATH}${chosen.slug}`);
        expect(loginHits).toEqual([]);
        expect(await storedAutoRedirectProviderId(api)).toBe(chosen.id);
        return;
      }

      await page.waitForURL(isBareLoginUrl);
      await expectFullLoginPage(loginPage);
      expect(ssoHits).toEqual([]);
    });
  }
});
