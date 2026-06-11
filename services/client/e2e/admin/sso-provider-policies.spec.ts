import { expect, test } from '../fixtures/auth.fixture';
import type { OAuthProviderPayload } from '../helpers/api/resources/OAuthApi';

const SLUG_PREFIX = 'e2e-sso';

const uniqueSlug = () =>
  `${SLUG_PREFIX}-${Date.now()}-${Math.floor(Math.random() * 1000)}`;

const providerPayload = (
  slug: string,
  overrides: Partial<OAuthProviderPayload> = {},
): OAuthProviderPayload => ({
  name: 'E2E Policy Provider',
  slug,
  type: 'oidc',
  clientId: 'e2e-client-id',
  clientSecret: 'e2e-client-secret',
  discoveryUrl: 'https://sso-e2e.example.com',
  scopes: 'openid email profile',
  enabled: true,
  registrationPolicy: 'inherit',
  approvalPolicy: 'inherit',
  ...overrides,
});

test.describe('Admin SSO provider policies', { tag: '@serial' }, () => {
  test.afterEach(async ({ api }) => {
    const providers = await api.oauth.listProviders();
    const created = providers.filter((provider) =>
      provider.slug.startsWith(`${SLUG_PREFIX}-`),
    );

    for (const provider of created) {
      await api.oauth.deleteProvider(provider.id);
    }
  });

  test('creates a provider with explicit policies and shows the open registration badge', async ({
    ssoSettingsPage,
  }) => {
    const slug = uniqueSlug();

    await ssoSettingsPage.gotoNew();
    await ssoSettingsPage.selectCustomProvider();

    await ssoSettingsPage.fillCustomProviderForm({
      name: 'E2E Policy Provider',
      slug,
      discoveryUrl: 'https://sso-e2e.example.com',
      clientId: 'e2e-client-id',
      clientSecret: 'e2e-client-secret',
    });

    await ssoSettingsPage.selectRegistrationPolicy('Allowed');
    await ssoSettingsPage.selectApprovalPolicy('Auto-approve');

    await expect(
      ssoSettingsPage.registrationPolicyRadio('Allowed'),
    ).toHaveAttribute('aria-checked', 'true');
    await expect(
      ssoSettingsPage.approvalPolicyRadio('Auto-approve'),
    ).toHaveAttribute('aria-checked', 'true');

    await ssoSettingsPage.addProviderButton.click();
    await ssoSettingsPage.waitForList();

    const row = ssoSettingsPage.providerRow(slug);
    await expect(row).toBeVisible();
    await expect(row.getByText('Open registration')).toBeVisible();
  });

  test('reflects persisted policies on edit and disables approval when registration is blocked', async ({
    page,
    api,
    ssoSettingsPage,
  }) => {
    const slug = uniqueSlug();
    const id = await api.oauth.createProvider(
      providerPayload(slug, {
        registrationPolicy: 'allowed',
        approvalPolicy: 'none',
      }),
    );

    await ssoSettingsPage.gotoEdit(id);

    await expect(
      ssoSettingsPage.registrationPolicyRadio('Allowed'),
    ).toHaveAttribute('aria-checked', 'true');
    await expect(
      ssoSettingsPage.approvalPolicyRadio('Auto-approve'),
    ).toHaveAttribute('aria-checked', 'true');

    await ssoSettingsPage.selectRegistrationPolicy('Blocked');

    await expect(
      page.getByText('Only existing users can sign in with this provider'),
    ).toBeVisible();
    await expect(
      page.getByText(
        'No effect while registration is blocked for this provider',
      ),
    ).toBeVisible();
    await expect(ssoSettingsPage.approvalPolicyRadio('Global')).toBeDisabled();
    await expect(
      ssoSettingsPage.approvalPolicyRadio('Required'),
    ).toBeDisabled();
    await expect(
      ssoSettingsPage.approvalPolicyRadio('Auto-approve'),
    ).toBeDisabled();

    await ssoSettingsPage.updateProviderButton.click();
    await ssoSettingsPage.waitForList();

    const row = ssoSettingsPage.providerRow(slug);
    await expect(row.getByText('Sign-in only')).toBeVisible();
    await expect(row.getByText('Open registration')).toBeHidden();
  });

  test('shows no badge when both policies are set back to global', async ({
    api,
    ssoSettingsPage,
  }) => {
    const slug = uniqueSlug();
    const id = await api.oauth.createProvider(
      providerPayload(slug, {
        registrationPolicy: 'allowed',
        approvalPolicy: 'required',
      }),
    );

    await ssoSettingsPage.gotoEdit(id);

    await ssoSettingsPage.selectApprovalPolicy('Global');
    await ssoSettingsPage.selectRegistrationPolicy('Global');

    await ssoSettingsPage.updateProviderButton.click();
    await ssoSettingsPage.waitForList();

    const row = ssoSettingsPage.providerRow(slug);
    await expect(row).toBeVisible();
    await expect(row.getByText('Open registration')).toBeHidden();
    await expect(row.getByText('Sign-in only')).toBeHidden();
  });

  test('disables admin approval when registration inherits a disabled global setting', async ({
    page,
    api,
    settingsApi,
    ssoSettingsPage,
  }) => {
    const slug = uniqueSlug();
    const id = await api.oauth.createProvider(providerPayload(slug));

    await settingsApi.set('user', { allowRegistration: false });

    await ssoSettingsPage.gotoEdit(id);

    await expect(
      ssoSettingsPage.registrationPolicyRadio('Global'),
    ).toHaveAttribute('aria-checked', 'true');
    await expect(
      page.getByText(
        'Follows the global registration setting, currently disabled',
      ),
    ).toBeVisible();
    await expect(
      page.getByText(
        'No effect while registration is blocked for this provider',
      ),
    ).toBeVisible();
    await expect(ssoSettingsPage.approvalPolicyRadio('Global')).toBeDisabled();
    await expect(
      ssoSettingsPage.approvalPolicyRadio('Required'),
    ).toBeDisabled();
    await expect(
      ssoSettingsPage.approvalPolicyRadio('Auto-approve'),
    ).toBeDisabled();
  });

  test('keeps the admin approval switch visible when registration is disabled', async ({
    page,
    settingsApi,
    adminSettingsPage,
  }) => {
    await settingsApi.set('user', { allowRegistration: false });

    await adminSettingsPage.gotoSecurity();
    await expect(adminSettingsPage.heading).toBeVisible();

    await expect(adminSettingsPage.allowRegistrationSwitch).toHaveAttribute(
      'aria-checked',
      'false',
    );
    await expect(page.getByText('Require Admin Approval')).toBeVisible();
    await expect(adminSettingsPage.approvalRequiredSwitch).toBeVisible();
    await expect(page.getByText('Minimum Password Length')).toBeHidden();
  });
});
