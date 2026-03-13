import { OAuthProviderRegistry } from './OAuthProviderConfig.svelte';

export type { OAuthProvider } from './OAuthProviderConfig.svelte';

export const OAuthProviderConfig = new OAuthProviderRegistry();
