import type { UserPreferencesResponse } from '@slink/api/Response/User/UserPreferencesResponse';

import type { GlobalSettings } from '@slink/lib/settings/Type/GlobalSettings';

export interface UploadPolicy {
  stripExif: boolean;
  allowOnlyPublicImages: boolean;
  defaultVisibility: UserPreferencesResponse['image.defaultVisibility'];
}

const resolveStripExif = (
  globalSettings: GlobalSettings | null,
  userPreferences: UserPreferencesResponse | null,
): boolean => {
  const override =
    userPreferences?.['image.stripExifMetadataOverride'] ?? 'default';

  if (override === 'default') {
    return globalSettings?.image?.stripExifMetadata ?? true;
  }

  return override === 'strip';
};

export const resolveUploadPolicy = (
  globalSettings: GlobalSettings | null,
  userPreferences: UserPreferencesResponse | null,
): UploadPolicy => ({
  stripExif: resolveStripExif(globalSettings, userPreferences),
  allowOnlyPublicImages: globalSettings?.image?.allowOnlyPublicImages ?? false,
  defaultVisibility: userPreferences?.['image.defaultVisibility'] ?? null,
});
