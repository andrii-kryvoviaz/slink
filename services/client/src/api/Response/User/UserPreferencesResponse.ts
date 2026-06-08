import type { LandingPage } from '@slink/lib/enum/LandingPage';

export type UserPreferencesResponse = {
  'license.default': string | null;
  'navigation.landingPage': LandingPage | null;
  'image.defaultVisibility': 'public' | 'private' | null;
  'image.externalUploadAutoPublish': boolean | null;
  'display.language': string | null;
};
