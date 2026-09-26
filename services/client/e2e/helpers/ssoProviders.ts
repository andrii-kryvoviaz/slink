import type { ApiClient } from './api';
import type { OAuthProviderPayload } from './api/resources/OAuthApi';

export function uniqueSsoSlug(prefix: string): string {
  return `${prefix}-${Date.now()}-${Math.floor(Math.random() * 1000)}`;
}

export function ssoProviderPayload(
  slug: string,
  overrides: Partial<OAuthProviderPayload> = {},
): OAuthProviderPayload {
  return {
    name: `E2E Provider ${slug}`,
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
  };
}

export async function createSsoProvider(
  api: ApiClient,
  prefix: string,
  overrides: Partial<OAuthProviderPayload> = {},
): Promise<{ id: string; slug: string; name: string }> {
  const slug = uniqueSsoSlug(prefix);
  const payload = ssoProviderPayload(slug, overrides);
  const id = await api.oauth.createProvider(payload);

  return { id, slug, name: payload.name };
}

export async function storedAutoRedirectProviderId(
  api: ApiClient,
): Promise<string | null> {
  const settings = await api.settings.getSettings();

  return settings.user.autoRedirectProviderId;
}

export async function deleteSsoProviders(
  api: ApiClient,
  prefix: string,
): Promise<void> {
  const providers = await api.oauth.listProviders();
  const created = providers.filter((provider) =>
    provider.slug.startsWith(`${prefix}-`),
  );

  for (const provider of created) {
    await api.oauth.deleteProvider(provider.id);
  }
}
