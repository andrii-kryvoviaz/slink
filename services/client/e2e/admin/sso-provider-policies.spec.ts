import { expect, test } from '../fixtures/auth.fixture';
import {
  deleteSsoProviders,
  ssoProviderPayload,
  uniqueSsoSlug,
} from '../helpers/ssoProviders';

const SLUG_PREFIX = 'e2e-sso';

test.describe('Admin SSO provider policies', { tag: '@serial' }, () => {
  test.afterEach(async ({ api }) => {
    await deleteSsoProviders(api, SLUG_PREFIX);
  });

  test('creates a provider with explicit policies and shows the open registration badge', async ({
    ssoSettingsPage,
  }) => {
    const slug = uniqueSsoSlug(SLUG_PREFIX);

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
    api,
    ssoSettingsPage,
  }) => {
    const slug = uniqueSsoSlug(SLUG_PREFIX);
    const id = await api.oauth.createProvider(
      ssoProviderPayload(slug, {
        registrationPolicy: 'allowed',
        approvalPolicy: 'none',
      }),
    );

    await ssoSettingsPage.gotoEdit(id);

    await expect(ssoSettingsPage.callbackUrlGuidance).toBeVisible();
    await expect(ssoSettingsPage.callbackUrlChip).toBeVisible();
    await expect(
      ssoSettingsPage.registrationPolicyRadio('Allowed'),
    ).toHaveAttribute('aria-checked', 'true');
    await expect(
      ssoSettingsPage.approvalPolicyRadio('Auto-approve'),
    ).toHaveAttribute('aria-checked', 'true');

    await ssoSettingsPage.selectRegistrationPolicy('Blocked');

    await expect(
      ssoSettingsPage.registrationPolicyRadio('Blocked'),
    ).toHaveAttribute('aria-checked', 'true');

    await expect(ssoSettingsPage.approvalPolicyRadio('Inherit')).toBeDisabled();
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
    const slug = uniqueSsoSlug(SLUG_PREFIX);
    const id = await api.oauth.createProvider(
      ssoProviderPayload(slug, {
        registrationPolicy: 'allowed',
        approvalPolicy: 'required',
      }),
    );

    await ssoSettingsPage.gotoEdit(id);

    await ssoSettingsPage.selectApprovalPolicy('Inherit');
    await ssoSettingsPage.selectRegistrationPolicy('Inherit');

    await ssoSettingsPage.updateProviderButton.click();
    await ssoSettingsPage.waitForList();

    const row = ssoSettingsPage.providerRow(slug);
    await expect(row).toBeVisible();
    await expect(row.getByText('Open registration')).toBeHidden();
    await expect(row.getByText('Sign-in only')).toBeHidden();
  });

  test('disables admin approval when registration inherits a disabled global setting', async ({
    api,
    settingsApi,
    ssoSettingsPage,
  }) => {
    const slug = uniqueSsoSlug(SLUG_PREFIX);
    const id = await api.oauth.createProvider(ssoProviderPayload(slug));

    await settingsApi.set('user', { allowRegistration: false });

    await ssoSettingsPage.gotoEdit(id);

    await expect(
      ssoSettingsPage.registrationPolicyRadio('Inherit'),
    ).toHaveAttribute('aria-checked', 'true');

    await expect(ssoSettingsPage.approvalPolicyRadio('Inherit')).toBeDisabled();
    await expect(
      ssoSettingsPage.approvalPolicyRadio('Required'),
    ).toBeDisabled();
    await expect(
      ssoSettingsPage.approvalPolicyRadio('Auto-approve'),
    ).toBeDisabled();
  });

  test('shows policy option details in the hover card', async ({
    page,
    api,
    ssoSettingsPage,
  }) => {
    const slug = uniqueSsoSlug(SLUG_PREFIX);
    const id = await api.oauth.createProvider(
      ssoProviderPayload(slug, { registrationPolicy: 'blocked' }),
    );

    await ssoSettingsPage.gotoEdit(id);

    await ssoSettingsPage.openRegistrationPolicyInfo();
    const card = page.locator('[data-slot="hover-card-content"]');
    await expect(
      card.getByText('Inherits the global registration setting.'),
    ).toBeVisible();
    await expect(
      card.getByText(
        'New users signing in with this provider get an account automatically.',
      ),
    ).toBeVisible();
    await expect(
      card.getByText('Only existing users can sign in with this provider.'),
    ).toBeVisible();
    await expect(card.getByText('Current')).toBeVisible();

    await ssoSettingsPage.closeHoverCards();

    await ssoSettingsPage.openApprovalPolicyInfo();
    await expect(
      page.getByText(
        'Has no effect while registration is blocked for this provider.',
      ),
    ).toBeVisible();
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
