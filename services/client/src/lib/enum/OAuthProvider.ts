import type { OAuthProviderFormData } from '@slink/api/Resources/OAuthResource';

export const OAuthProvider = {
  Google: 'google',
  Authentik: 'authentik',
  Keycloak: 'keycloak',
  Authelia: 'authelia',
} as const;

// eslint-disable-next-line @typescript-eslint/no-redeclare
export type OAuthProvider = (typeof OAuthProvider)[keyof typeof OAuthProvider];

export type OAuthProviderConfig = {
  readonly slug: OAuthProvider;
  readonly name: string;
  readonly icon: string;
  readonly scopes: string;
  readonly fields: readonly (keyof OAuthProviderFormData)[];
  readonly discoveryUrl?: string;
  readonly discoveryPlaceholder?: string;
};

const OAUTH_PROVIDER_CONFIG = {
  [OAuthProvider.Google]: {
    slug: OAuthProvider.Google,
    name: 'Google',
    icon: 'logos:google-icon',
    scopes: 'openid email profile',
    fields: ['clientId', 'clientSecret'],
    discoveryUrl: 'https://accounts.google.com',
  },
  [OAuthProvider.Authentik]: {
    slug: OAuthProvider.Authentik,
    name: 'Authentik',
    icon: 'simple-icons:authentik',
    scopes: 'openid email profile',
    fields: ['discoveryUrl', 'clientId', 'clientSecret'],
    discoveryPlaceholder: 'https://auth.example.com/application/o/my-app',
  },
  [OAuthProvider.Keycloak]: {
    slug: OAuthProvider.Keycloak,
    name: 'Keycloak',
    icon: 'simple-icons:keycloak',
    scopes: 'openid email profile',
    fields: ['discoveryUrl', 'clientId', 'clientSecret'],
    discoveryPlaceholder: 'https://keycloak.example.com/realms/my-realm',
  },
  [OAuthProvider.Authelia]: {
    slug: OAuthProvider.Authelia,
    name: 'Authelia',
    icon: 'simple-icons:authelia',
    scopes: 'openid email profile',
    fields: ['discoveryUrl', 'clientId', 'clientSecret'],
    discoveryPlaceholder: 'https://auth.example.com',
  },
} as const satisfies Record<OAuthProvider, OAuthProviderConfig>;

export function getProviderConfig(slug: OAuthProvider): OAuthProviderConfig {
  return OAUTH_PROVIDER_CONFIG[slug];
}

export const oauthProviders: readonly OAuthProviderConfig[] = Object.values(
  OAUTH_PROVIDER_CONFIG,
);
