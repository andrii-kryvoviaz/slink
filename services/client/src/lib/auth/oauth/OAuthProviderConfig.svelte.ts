import type { OAuthProviderFormData } from '@slink/api/Resources/OAuthResource';

export type OAuthProvider = {
  slug: string;
  name: string;
  scopes: string;
  fields: readonly (keyof OAuthProviderFormData)[];
  discoveryUrl: string;
  discoveryPlaceholder: string;
  icon: string;
  isCustom: boolean;
};

export class OAuthProviderRegistry {
  private static readonly PRESETS: OAuthProvider[] = [
    {
      slug: 'google',
      name: 'Google',
      scopes: 'openid email profile',
      fields: ['clientId', 'clientSecret'],
      discoveryUrl: 'https://accounts.google.com',
      discoveryPlaceholder: '',
      icon: 'logos:google-icon',
      isCustom: false,
    },
    {
      slug: 'authentik',
      name: 'Authentik',
      scopes: 'openid email profile',
      fields: ['discoveryUrl', 'clientId', 'clientSecret'],
      discoveryUrl: '',
      discoveryPlaceholder: 'https://auth.example.com/application/o/my-app',
      icon: 'simple-icons:authentik',
      isCustom: false,
    },
    {
      slug: 'keycloak',
      name: 'Keycloak',
      scopes: 'openid email profile',
      fields: ['discoveryUrl', 'clientId', 'clientSecret'],
      discoveryUrl: '',
      discoveryPlaceholder: 'https://keycloak.example.com/realms/my-realm',
      icon: 'simple-icons:keycloak',
      isCustom: false,
    },
    {
      slug: 'authelia',
      name: 'Authelia',
      scopes: 'openid email profile',
      fields: ['discoveryUrl', 'clientId', 'clientSecret'],
      discoveryUrl: '',
      discoveryPlaceholder: 'https://auth.example.com',
      icon: 'simple-icons:authelia',
      isCustom: false,
    },
    {
      slug: 'pocketid',
      name: 'Pocket ID',
      scopes: 'openid email profile',
      fields: ['discoveryUrl', 'clientId', 'clientSecret'],
      discoveryUrl: '',
      discoveryPlaceholder: 'https://pocket-id.example.com',
      icon: 'simple-icons:pocket',
      isCustom: false,
    },
    {
      slug: 'custom',
      name: 'Custom',
      scopes: 'openid email profile',
      fields: ['name', 'slug', 'discoveryUrl', 'clientId', 'clientSecret'],
      discoveryUrl: '',
      discoveryPlaceholder: 'https://idp.example.com',
      icon: 'ph:key',
      isCustom: true,
    },
  ];

  private readonly _presets: readonly OAuthProvider[];
  private readonly _map: Map<string, OAuthProvider>;
  private readonly _custom: OAuthProvider;

  constructor() {
    this._presets = OAuthProviderRegistry.PRESETS;
    this._map = new Map(this._presets.map((p) => [p.slug, p]));
    this._custom = this._presets.find((p) => p.isCustom)!;
  }

  all(): readonly OAuthProvider[] {
    return this._presets;
  }

  hasPreset(slug: string): boolean {
    return this._map.has(slug);
  }

  resolve(slug: string): OAuthProvider {
    return this._map.get(slug) ?? { ...this._custom, slug };
  }
}
