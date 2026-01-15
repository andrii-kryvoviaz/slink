import type { LandingPage } from '@slink/lib/enum/LandingPage';

export type UserPreferencesResponse = {
  'license.default': string | null;
  'navigation.landingPage': LandingPage | null;
};
