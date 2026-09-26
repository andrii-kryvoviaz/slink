import type { Locator, Page, Route } from '@playwright/test';

import { expect, test } from '../fixtures/auth.fixture';
import type { ApiClient } from '../helpers/api';
import {
  createSsoProvider,
  deleteSsoProviders,
  storedAutoRedirectProviderId,
} from '../helpers/ssoProviders';

const SLUG_PREFIX = 'e2e-autoredirect';

const countSettingsPosts = (page: Page) => {
  const counter = { posts: 0 };
  page.on('request', (request) => {
    if (
      request.method() === 'POST' &&
      request.url().includes('/api/settings')
    ) {
      counter.posts++;
    }
  });
  return counter;
};

const expectNoEnabledProviders = async (api: ApiClient) => {
  const providers = await api.oauth.listProviders();
  expect(providers.filter((provider) => provider.enabled)).toEqual([]);
};

const toggleProvider = async (page: Page, row: Locator, id: string) => {
  const toggle = row.getByRole('switch');
  const saved = page.waitForResponse(
    (response) =>
      response.url().includes(`/api/admin/oauth/providers/${id}`) &&
      ['PATCH', 'PUT'].includes(response.request().method()),
  );
  await toggle.click();
  await saved;
};

const holdFirstSettingsPost = async (
  page: Page,
  settle: (route: Route) => Promise<void> = (route) => route.continue(),
) => {
  const gate = Promise.withResolvers<void>();
  const intercepted = Promise.withResolvers<void>();
  let held = false;

  await page.route('**/api/settings', async (route) => {
    if (route.request().method() !== 'POST' || held) return route.continue();
    held = true;
    intercepted.resolve();
    await gate.promise;
    await settle(route);
  });

  return { intercepted: intercepted.promise, release: gate.resolve };
};

const attemptBlockedPick = async (page: Page, trigger: Locator) => {
  await expect(trigger).toHaveAttribute('aria-disabled', 'true');
  await expect(trigger).not.toHaveAttribute('disabled');
  await trigger.click({ force: true });
  await expect(page.getByRole('option')).toHaveCount(0);
};

test.describe('Admin SSO auto-redirect setting', { tag: '@serial' }, () => {
  test.afterEach(({ api }) => deleteSsoProviders(api, SLUG_PREFIX));

  test('offers None first, then enabled providers only', async ({
    page,
    api,
    settingsApi,
    ssoSettingsPage,
  }) => {
    await settingsApi.set('user', { autoRedirectProviderId: null });

    const enabledA = await createSsoProvider(api, SLUG_PREFIX);
    const enabledB = await createSsoProvider(api, SLUG_PREFIX);
    const disabledC = await createSsoProvider(api, SLUG_PREFIX, {
      enabled: false,
    });

    await ssoSettingsPage.gotoList();

    const options = page.getByRole('option');
    await ssoSettingsPage.clickUntil(
      ssoSettingsPage.autoRedirectTrigger,
      options.first(),
    );

    await expect(options.first()).toHaveText('None');
    await expect(
      page.getByRole('option', { name: enabledA.name, exact: true }),
    ).toBeVisible();
    await expect(
      page.getByRole('option', { name: enabledB.name, exact: true }),
    ).toBeVisible();
    await expect(
      page.getByRole('option', { name: disabledC.name, exact: true }),
    ).toHaveCount(0);

    const created = [enabledA.name, enabledB.name];
    const expectedOrder = (await api.oauth.listProviders())
      .filter((provider) => provider.enabled && created.includes(provider.name))
      .map((provider) => provider.name);
    const renderedOrder = (await options.allTextContents())
      .map((text) => text.trim())
      .filter((text) => created.includes(text));

    expect(renderedOrder).toEqual(expectedOrder);

    await expect(
      page.locator(
        '[role="option"][aria-disabled="true"], [role="option"][data-disabled]',
      ),
    ).toHaveCount(0);
  });

  test('renders the setting below the provider list', async ({
    api,
    settingsApi,
    ssoSettingsPage,
  }) => {
    await settingsApi.set('user', { autoRedirectProviderId: null });

    const provider = await createSsoProvider(api, SLUG_PREFIX);

    await ssoSettingsPage.gotoList();

    await expect(ssoSettingsPage.autoRedirectTrigger).toBeVisible();
    await expect(ssoSettingsPage.providerRow(provider.slug)).toBeVisible();

    const triggerBox = await ssoSettingsPage.autoRedirectTrigger.boundingBox();
    const rowBox = await ssoSettingsPage
      .providerRow(provider.slug)
      .boundingBox();

    expect(triggerBox!.y).toBeGreaterThan(rowBox!.y);
  });

  test('labels the footer row with the auto-redirect hint', async ({
    api,
    settingsApi,
    ssoSettingsPage,
  }) => {
    await settingsApi.set('user', { autoRedirectProviderId: null });

    await createSsoProvider(api, SLUG_PREFIX);

    await ssoSettingsPage.gotoList();

    await expect(ssoSettingsPage.autoRedirectRow).toContainText(
      'Auto-redirect',
    );
    await expect(ssoSettingsPage.autoRedirectRow).toContainText(
      'Skip the login page and send visitors to this provider',
    );
    await expect(ssoSettingsPage.autoRedirectRow).not.toContainText(
      'The full login page appears only when the provider fails',
    );
  });

  test('shows the header chip without the old notice or save pane', async ({
    page,
    baseURL,
    api,
    settingsApi,
    ssoSettingsPage,
  }) => {
    await settingsApi.set('user', { autoRedirectProviderId: null });

    const provider = await createSsoProvider(api, SLUG_PREFIX);

    await ssoSettingsPage.gotoList();

    await expect(
      page.getByRole('heading', { name: 'Single sign-on' }),
    ).toBeVisible();
    const description = page.getByText(
      'Identity providers your users can sign in with',
      { exact: true },
    );
    await expect(description).toBeVisible();
    await expect(ssoSettingsPage.callbackUrlChip).toContainText('Callback URL');
    await expect(ssoSettingsPage.callbackUrlChip).toContainText(
      new URL('/profile/sso/callback', baseURL).href,
    );
    await expect(page.getByRole('button', { name: 'Create' })).toBeVisible();
    await expect(ssoSettingsPage.providerRow(provider.slug)).toBeVisible();

    const descriptionBox = await description.boundingBox();
    const chipBox = await ssoSettingsPage.callbackUrlChip.boundingBox();
    const rowBox = await ssoSettingsPage
      .providerRow(provider.slug)
      .boundingBox();

    expect(chipBox!.y).toBeGreaterThan(descriptionBox!.y);
    expect(chipBox!.y).toBeLessThan(rowBox!.y);

    await expect(
      page.getByRole('heading', { name: 'Single Sign-On', exact: true }),
    ).toHaveCount(0);
    await expect(
      page.getByText('Manage SSO/OIDC identity providers'),
    ).toHaveCount(0);
    await expect(page.getByText('Login Redirect')).toHaveCount(0);
    await expect(page.getByText('Auto-redirect Provider')).toHaveCount(0);
    await expect(page.getByText(/allowed redirect URIs/)).toHaveCount(0);
    await expect(
      page.getByRole('button', { name: 'Save Changes' }),
    ).toHaveCount(0);
  });

  test('hides the footer row when every provider is disabled', async ({
    api,
    settingsApi,
    ssoSettingsPage,
  }) => {
    await settingsApi.set('user', { autoRedirectProviderId: null });

    const disabledA = await createSsoProvider(api, SLUG_PREFIX, {
      enabled: false,
    });
    const disabledB = await createSsoProvider(api, SLUG_PREFIX, {
      enabled: false,
    });
    await expectNoEnabledProviders(api);

    await ssoSettingsPage.gotoList();

    await expect(ssoSettingsPage.providerRow(disabledA.slug)).toBeVisible();
    await expect(ssoSettingsPage.providerRow(disabledB.slug)).toBeVisible();
    await expect(ssoSettingsPage.autoRedirectTrigger).toHaveCount(0);
    await expect(
      ssoSettingsPage.page.getByText('Auto-redirect', { exact: true }),
    ).toHaveCount(0);
  });

  test('hides the footer row on the empty state', async ({
    page,
    api,
    settingsApi,
    ssoSettingsPage,
  }) => {
    await settingsApi.set('user', { autoRedirectProviderId: null });

    expect(await api.oauth.listProviders()).toEqual([]);

    await ssoSettingsPage.gotoList();

    await expect(
      page.getByText('No SSO providers configured yet'),
    ).toBeVisible();
    await expect(ssoSettingsPage.autoRedirectTrigger).toHaveCount(0);
    await expect(ssoSettingsPage.callbackUrlChip).toBeVisible();
  });

  test('persists the chosen provider and keeps it after reload', async ({
    page,
    api,
    settingsApi,
    ssoSettingsPage,
  }) => {
    await settingsApi.set('user', { autoRedirectProviderId: null });

    const provider = await createSsoProvider(api, SLUG_PREFIX);

    const counter = countSettingsPosts(page);

    await ssoSettingsPage.gotoList();
    const response = await ssoSettingsPage.selectAutoRedirect(provider.name);

    expect(response.ok()).toBe(true);
    expect(response.request().postDataJSON().category).toBe('user');
    await expect(ssoSettingsPage.autoRedirectTrigger).toContainText(
      provider.name,
    );
    await expect(ssoSettingsPage.getToast()).toHaveCount(0);

    await expect
      .poll(() => storedAutoRedirectProviderId(api))
      .toBe(provider.id);
    expect(counter.posts).toBe(1);
    await expect(ssoSettingsPage.getToast()).toHaveCount(0);
    await expect(
      page.getByRole('button', { name: 'Save Changes' }),
    ).toHaveCount(0);

    await page.reload();

    await expect(ssoSettingsPage.autoRedirectTrigger).toContainText(
      provider.name,
    );
  });

  test('stores null when None is chosen', async ({
    page,
    api,
    settingsApi,
    ssoSettingsPage,
  }) => {
    await settingsApi.set('user', { autoRedirectProviderId: null });

    const provider = await createSsoProvider(api, SLUG_PREFIX);
    await settingsApi.set('user', { autoRedirectProviderId: provider.id });

    await ssoSettingsPage.gotoList();
    await expect(ssoSettingsPage.autoRedirectTrigger).toContainText(
      provider.name,
    );

    await ssoSettingsPage.selectAutoRedirect('None');

    await expect.poll(() => storedAutoRedirectProviderId(api)).toBeNull();

    await page.reload();

    await expect(ssoSettingsPage.autoRedirectTrigger).toContainText('None');
  });

  test('keeps the other user settings when saving from the SSO page', async ({
    api,
    settingsApi,
    ssoSettingsPage,
    adminSettingsPage,
  }) => {
    await settingsApi.set('user', {
      allowRegistration: false,
      approvalRequired: true,
      autoRedirectProviderId: null,
    });

    const provider = await createSsoProvider(api, SLUG_PREFIX);
    const before = (await api.settings.getSettings()).user;

    await ssoSettingsPage.gotoList();
    await ssoSettingsPage.selectAutoRedirect(provider.name);

    await expect
      .poll(() => storedAutoRedirectProviderId(api))
      .toBe(provider.id);

    const after = (await api.settings.getSettings()).user;

    expect({ ...after, autoRedirectProviderId: null }).toEqual(before);

    await adminSettingsPage.gotoSecurity();

    await expect(adminSettingsPage.allowRegistrationSwitch).toHaveAttribute(
      'aria-checked',
      'false',
    );
    await expect(adminSettingsPage.approvalRequiredSwitch).toHaveAttribute(
      'aria-checked',
      'true',
    );
  });

  test('keeps the provider list mounted when the setting is saved on change', async ({
    page,
    api,
    settingsApi,
    ssoSettingsPage,
  }) => {
    await settingsApi.set('user', { autoRedirectProviderId: null });

    const target = await createSsoProvider(api, SLUG_PREFIX);
    const toggled = await createSsoProvider(api, SLUG_PREFIX);

    await ssoSettingsPage.gotoList();

    const row = ssoSettingsPage.providerRow(toggled.slug);
    await expect(row).toBeVisible();

    const toggle = row.getByRole('switch');
    await toggleProvider(page, row, toggled.id);
    await expect(toggle).toHaveAttribute('aria-checked', 'false');

    const handle = await row.elementHandle();

    await handle!.evaluate((node) => {
      const flags = window as unknown as Record<string, boolean>;
      flags.__ssoSkeletonSeen = false;
      flags.__ssoRowDetached = false;
      new MutationObserver(() => {
        if (document.querySelector('.animate-pulse.divide-y')) {
          flags.__ssoSkeletonSeen = true;
        }
        if (!node.isConnected) {
          flags.__ssoRowDetached = true;
        }
      }).observe(document.body, { childList: true, subtree: true });
    });

    const rerun = page.waitForResponse((response) =>
      response.url().includes('__data.json'),
    );
    await ssoSettingsPage.selectAutoRedirect(target.name);

    await expect.poll(() => storedAutoRedirectProviderId(api)).toBe(target.id);
    await rerun;
    await expect(ssoSettingsPage.getToast()).toHaveCount(0);

    expect(await handle!.evaluate((node) => node.isConnected)).toBe(true);
    expect(
      await page.evaluate(() => {
        const flags = window as unknown as Record<string, boolean>;
        return [flags.__ssoSkeletonSeen, flags.__ssoRowDetached];
      }),
    ).toEqual([false, false]);
    await expect(toggle).toHaveAttribute('aria-checked', 'false');
  });

  test('shows None when no provider is stored', async ({
    api,
    settingsApi,
    ssoSettingsPage,
  }) => {
    await settingsApi.set('user', { autoRedirectProviderId: null });

    await createSsoProvider(api, SLUG_PREFIX);

    await ssoSettingsPage.gotoList();

    await expect(ssoSettingsPage.autoRedirectTrigger).toContainText('None');
  });

  test('leaves a stale provider id untouched', async ({
    page,
    api,
    settingsApi,
    ssoSettingsPage,
  }) => {
    await settingsApi.set('user', { autoRedirectProviderId: null });

    const provider = await createSsoProvider(api, SLUG_PREFIX);
    await createSsoProvider(api, SLUG_PREFIX);
    await settingsApi.set('user', { autoRedirectProviderId: provider.id });
    await api.oauth.updateProvider(provider.id, { enabled: false });

    const counter = countSettingsPosts(page);

    await ssoSettingsPage.gotoList();
    await expect(ssoSettingsPage.autoRedirectTrigger).toContainText(
      'Select provider',
    );

    const none = page.getByRole('option', { name: 'None', exact: true });
    await ssoSettingsPage.clickUntil(ssoSettingsPage.autoRedirectTrigger, none);

    await expect(
      page.getByRole('option', { name: provider.name, exact: true }),
    ).toHaveCount(0);

    await page.keyboard.press('Escape');
    await expect(page.getByRole('option')).toHaveCount(0);

    expect(counter.posts).toBe(0);
    expect(await storedAutoRedirectProviderId(api)).toBe(provider.id);
  });

  test('sends nothing when the select is opened and dismissed', async ({
    page,
    api,
    settingsApi,
    ssoSettingsPage,
  }) => {
    await settingsApi.set('user', { autoRedirectProviderId: null });

    await createSsoProvider(api, SLUG_PREFIX);
    const counter = countSettingsPosts(page);

    await ssoSettingsPage.gotoList();

    const none = page.getByRole('option', { name: 'None', exact: true });
    await ssoSettingsPage.clickUntil(ssoSettingsPage.autoRedirectTrigger, none);
    await page.keyboard.press('Escape');
    await expect(page.getByRole('option')).toHaveCount(0);

    expect(counter.posts).toBe(0);
    expect(await storedAutoRedirectProviderId(api)).toBeNull();
    await expect(ssoSettingsPage.autoRedirectTrigger).toContainText('None');
  });

  test('reverts the pick when the save fails', async ({
    page,
    api,
    settingsApi,
    ssoSettingsPage,
  }) => {
    await settingsApi.set('user', { autoRedirectProviderId: null });

    const stored = await createSsoProvider(api, SLUG_PREFIX);
    const picked = await createSsoProvider(api, SLUG_PREFIX);
    await settingsApi.set('user', { autoRedirectProviderId: stored.id });

    await page.route('**/api/settings', (route) => {
      if (route.request().method() !== 'POST') return route.continue();
      return route.fulfill({
        status: 500,
        contentType: 'application/json',
        body: '{"error":{"message":"E2E forced failure"}}',
      });
    });

    await ssoSettingsPage.gotoList();
    await expect(ssoSettingsPage.autoRedirectTrigger).toContainText(
      stored.name,
    );

    const failed = await ssoSettingsPage.selectAutoRedirect(picked.name);

    expect(failed.status()).toBe(500);
    await ssoSettingsPage.waitForToast();
    await expect(ssoSettingsPage.autoRedirectTrigger).toContainText(
      stored.name,
    );
    expect(await storedAutoRedirectProviderId(api)).toBe(stored.id);

    await page.unroute('**/api/settings');

    const saved = await ssoSettingsPage.selectAutoRedirect(picked.name);

    expect(saved.ok()).toBe(true);
    await expect.poll(() => storedAutoRedirectProviderId(api)).toBe(picked.id);
    await expect(ssoSettingsPage.autoRedirectTrigger).toContainText(
      picked.name,
    );
  });

  test('blocks a second pick while the save is in flight', async ({
    page,
    api,
    settingsApi,
    ssoSettingsPage,
  }) => {
    await settingsApi.set('user', { autoRedirectProviderId: null });

    const first = await createSsoProvider(api, SLUG_PREFIX);
    const second = await createSsoProvider(api, SLUG_PREFIX);
    const trigger = ssoSettingsPage.autoRedirectTrigger;

    const counter = countSettingsPosts(page);
    const hold = await holdFirstSettingsPost(page);

    await ssoSettingsPage.gotoList();
    await expect(trigger).toContainText('None');

    const saved = ssoSettingsPage.selectAutoRedirect(first.name);
    await hold.intercepted;
    await expect(page.getByRole('option')).toHaveCount(0);

    await attemptBlockedPick(page, trigger);
    expect(counter.posts).toBe(1);
    await expect(trigger).not.toContainText(second.name);

    hold.release();
    expect((await saved).ok()).toBe(true);

    await expect.poll(() => storedAutoRedirectProviderId(api)).toBe(first.id);
    await expect(trigger).toBeEnabled();
    await expect(trigger).not.toHaveAttribute('aria-disabled', 'true');
    await expect(trigger).toContainText(first.name);
    expect(counter.posts).toBe(1);
    await expect(ssoSettingsPage.getToast()).toHaveCount(0);

    await page.unroute('**/api/settings');

    const again = await ssoSettingsPage.selectAutoRedirect(second.name);

    expect(again.ok()).toBe(true);
    await expect.poll(() => storedAutoRedirectProviderId(api)).toBe(second.id);
    await expect(trigger).toContainText(second.name);
    expect(counter.posts).toBe(2);

    await page.reload();

    await expect(trigger).toContainText(second.name);
  });

  test('blocks a second pick while a failing save is in flight', async ({
    page,
    api,
    settingsApi,
    ssoSettingsPage,
  }) => {
    await settingsApi.set('user', { autoRedirectProviderId: null });

    const stored = await createSsoProvider(api, SLUG_PREFIX);
    const picked = await createSsoProvider(api, SLUG_PREFIX);
    const other = await createSsoProvider(api, SLUG_PREFIX);
    await settingsApi.set('user', { autoRedirectProviderId: stored.id });
    const trigger = ssoSettingsPage.autoRedirectTrigger;

    const counter = countSettingsPosts(page);
    const hold = await holdFirstSettingsPost(page, (route) =>
      route.fulfill({
        status: 500,
        contentType: 'application/json',
        body: '{"error":{"message":"E2E forced failure"}}',
      }),
    );

    await ssoSettingsPage.gotoList();
    await expect(trigger).toContainText(stored.name);

    const failed = ssoSettingsPage.selectAutoRedirect(picked.name);
    await hold.intercepted;
    await expect(page.getByRole('option')).toHaveCount(0);

    await attemptBlockedPick(page, trigger);
    expect(counter.posts).toBe(1);
    await expect(trigger).not.toContainText(other.name);

    hold.release();
    expect((await failed).status()).toBe(500);
    await ssoSettingsPage.waitForToast();

    await expect(trigger).toContainText(stored.name);
    expect(await storedAutoRedirectProviderId(api)).toBe(stored.id);
    expect(counter.posts).toBe(1);

    await page.unroute('**/api/settings');

    const saved = await ssoSettingsPage.selectAutoRedirect(other.name);

    expect(saved.ok()).toBe(true);
    await expect.poll(() => storedAutoRedirectProviderId(api)).toBe(other.id);
    await expect(trigger).toContainText(other.name);
    expect(counter.posts).toBe(2);
  });

  test('rejects a pick from a list reopened before the save starts', async ({
    page,
    api,
    settingsApi,
    ssoSettingsPage,
  }) => {
    await settingsApi.set('user', { autoRedirectProviderId: null });

    const first = await createSsoProvider(api, SLUG_PREFIX);
    const second = await createSsoProvider(api, SLUG_PREFIX);
    const trigger = ssoSettingsPage.autoRedirectTrigger;

    const firstOption = page.getByRole('option', {
      name: first.name,
      exact: true,
    });
    const secondOption = page.getByRole('option', {
      name: second.name,
      exact: true,
    });

    const counter = countSettingsPosts(page);
    const hold = await holdFirstSettingsPost(page);

    await ssoSettingsPage.gotoWithPausedClock(async () => {
      await ssoSettingsPage.gotoList();
      await expect(trigger).toContainText('None');
    });

    await ssoSettingsPage.clickUntilOnPausedClock(trigger, firstOption);
    await firstOption.click();
    await expect(async () => {
      await page.clock.runFor(16);
      await expect(page.getByRole('option')).toHaveCount(0, { timeout: 100 });
    }).toPass({ timeout: 5000 });

    await page.keyboard.press('Enter');
    await expect(async () => {
      await page.clock.runFor(16);
      await expect(secondOption).toBeVisible({ timeout: 100 });
    }).toPass({ timeout: 5000 });
    expect(counter.posts).toBe(0);

    await page.clock.runFor(300);
    await hold.intercepted;

    await expect(secondOption).toBeVisible();
    const ariaDisabled = await secondOption.getAttribute('aria-disabled');
    const dataDisabled = await secondOption.getAttribute('data-disabled');
    expect(ariaDisabled === 'true' || dataDisabled !== null).toBe(true);
    await expect(trigger).toContainText(first.name);
    await expect(trigger).not.toContainText(second.name);
    expect(counter.posts).toBe(1);

    await secondOption.click({ force: true });

    hold.release();
    await page.clock.runFor(500);
    if (await page.getByRole('option').count()) {
      await page.keyboard.press('Escape');
    }

    await expect.poll(() => storedAutoRedirectProviderId(api)).toBe(first.id);
    await expect(trigger).toContainText(first.name);
    await page.clock.runFor(400);
    expect(counter.posts).toBe(1);

    await page.unroute('**/api/settings');
    await page.clock.resume();
    await page.reload();
    await expect(trigger).toContainText(first.name);
  });

  test('keeps the first pick after a failed save in another category', async ({
    page,
    api,
    settingsApi,
    ssoSettingsPage,
    adminSettingsPage,
  }) => {
    await settingsApi.set('user', { autoRedirectProviderId: null });

    const stored = await createSsoProvider(api, SLUG_PREFIX);
    const picked = await createSsoProvider(api, SLUG_PREFIX);
    await settingsApi.set('user', { autoRedirectProviderId: stored.id });

    await adminSettingsPage.gotoSecurity();

    await page.route('**/api/settings', (route) => {
      if (route.request().method() !== 'POST') return route.continue();
      if (route.request().postDataJSON().category !== 'access') {
        return route.continue();
      }
      return route.fulfill({
        status: 500,
        contentType: 'application/json',
        body: '{"error":{"message":"E2E forced failure"}}',
      });
    });

    await adminSettingsPage.toggleSetting('accessAllowGuestUploads');
    await adminSettingsPage.saveAccessSettings();
    await adminSettingsPage.waitForToast();
    await page.unroute('**/api/settings');

    await page.locator('a[href="/admin/settings/sso"]').first().click();
    const trigger = ssoSettingsPage.autoRedirectTrigger;
    await expect(trigger).toContainText(stored.name);

    const response = await ssoSettingsPage.selectAutoRedirect(picked.name);

    expect(response.ok()).toBe(true);
    const payload = response.request().postDataJSON();
    expect(payload.category).toBe('user');
    expect(payload.settings.autoRedirectProviderId).toBe(picked.id);

    await expect(trigger).toContainText(picked.name);
    await page.waitForTimeout(1000);
    await expect(trigger).toContainText(picked.name);
    await expect.poll(() => storedAutoRedirectProviderId(api)).toBe(picked.id);
  });

  test('keeps keyboard focus on the trigger through a save', async ({
    page,
    api,
    settingsApi,
    ssoSettingsPage,
  }) => {
    await settingsApi.set('user', { autoRedirectProviderId: null });

    const first = await createSsoProvider(api, SLUG_PREFIX);
    const second = await createSsoProvider(api, SLUG_PREFIX);
    const trigger = ssoSettingsPage.autoRedirectTrigger;

    const firstOption = page.getByRole('option', {
      name: first.name,
      exact: true,
    });
    const secondOption = page.getByRole('option', {
      name: second.name,
      exact: true,
    });

    const hold = await holdFirstSettingsPost(page);

    await ssoSettingsPage.gotoList();
    await expect(trigger).toContainText('None');

    await trigger.focus();
    await page.keyboard.press('Enter');

    await expect(async () => {
      await page.keyboard.press('ArrowDown');
      await expect(firstOption).toHaveAttribute('data-highlighted');
    }).toPass({ timeout: 15000 });

    await page.keyboard.press('Enter');

    await hold.intercepted;

    await expect(trigger).toBeFocused();
    await expect(trigger).toHaveAttribute('aria-disabled', 'true');
    await expect(trigger).not.toHaveAttribute('disabled');

    await page.keyboard.press('Enter');
    await expect(page.getByRole('option')).toHaveCount(0);

    hold.release();

    await expect.poll(() => storedAutoRedirectProviderId(api)).toBe(first.id);
    await expect(trigger).toBeFocused();
    await expect(trigger).toContainText(first.name);
    await expect(trigger).not.toHaveAttribute('aria-disabled', 'true');

    await page.keyboard.press('Enter');
    await expect(secondOption).toBeVisible();

    await page.keyboard.press('Escape');
    await expect(trigger).toBeFocused();

    await page.unroute('**/api/settings');
  });

  test('updates the options when a provider is toggled', async ({
    page,
    api,
    settingsApi,
    ssoSettingsPage,
  }) => {
    await settingsApi.set('user', { autoRedirectProviderId: null });

    const kept = await createSsoProvider(api, SLUG_PREFIX);
    const toggled = await createSsoProvider(api, SLUG_PREFIX);
    const counter = countSettingsPosts(page);

    await ssoSettingsPage.gotoList();
    await page.evaluate(() => {
      (window as unknown as Record<string, boolean>).__noReload = true;
    });

    const keptOption = page.getByRole('option', {
      name: kept.name,
      exact: true,
    });
    const toggledOption = page.getByRole('option', {
      name: toggled.name,
      exact: true,
    });
    const row = ssoSettingsPage.providerRow(toggled.slug);

    await toggleProvider(page, row, toggled.id);
    await expect(row.getByRole('switch')).toHaveAttribute(
      'aria-checked',
      'false',
    );

    await ssoSettingsPage.clickUntil(
      ssoSettingsPage.autoRedirectTrigger,
      keptOption,
    );
    await expect(toggledOption).toHaveCount(0);
    await page.keyboard.press('Escape');
    await expect(page.getByRole('option')).toHaveCount(0);

    await toggleProvider(page, row, toggled.id);
    await expect(row.getByRole('switch')).toHaveAttribute(
      'aria-checked',
      'true',
    );

    await ssoSettingsPage.clickUntil(
      ssoSettingsPage.autoRedirectTrigger,
      toggledOption,
    );
    await expect(keptOption).toBeVisible();

    const created = [kept.name, toggled.name];
    const expectedOrder = (await api.oauth.listProviders())
      .filter((provider) => provider.enabled && created.includes(provider.name))
      .map((provider) => provider.name);
    const renderedOrder = (await page.getByRole('option').allTextContents())
      .map((text) => text.trim())
      .filter((text) => created.includes(text));

    expect(renderedOrder).toEqual(expectedOrder);

    await page.keyboard.press('Escape');

    expect(
      await page.evaluate(
        () => (window as unknown as Record<string, boolean>).__noReload,
      ),
    ).toBe(true);
    expect(counter.posts).toBe(0);
  });

  test('hides the row when the last enabled provider is disabled', async ({
    page,
    api,
    settingsApi,
    ssoSettingsPage,
  }) => {
    await settingsApi.set('user', { autoRedirectProviderId: null });

    const provider = await createSsoProvider(api, SLUG_PREFIX);
    expect(
      (await api.oauth.listProviders())
        .filter((item) => item.enabled)
        .map((item) => item.id),
    ).toEqual([provider.id]);

    await ssoSettingsPage.gotoList();
    await expect(ssoSettingsPage.autoRedirectTrigger).toBeVisible();

    const row = ssoSettingsPage.providerRow(provider.slug);

    await toggleProvider(page, row, provider.id);

    await expect(ssoSettingsPage.autoRedirectTrigger).toHaveCount(0);
    await expect(row).toBeVisible();

    await toggleProvider(page, row, provider.id);

    await expect(ssoSettingsPage.autoRedirectTrigger).toContainText('None');
  });
});
