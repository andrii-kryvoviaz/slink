export const OAuthProvider = {
  Google: 'google',
  Authentik: 'authentik',
  Keycloak: 'keycloak',
  Authelia: 'authelia',
} as const;

// eslint-disable-next-line @typescript-eslint/no-redeclare
export type OAuthProvider = (typeof OAuthProvider)[keyof typeof OAuthProvider];
