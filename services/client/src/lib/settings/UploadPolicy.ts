import type { UserPreferencesResponse } from '@slink/api/Response/User/UserPreferencesResponse';

import type { GlobalSettings } from '@slink/lib/settings/Type/GlobalSettings';

export interface UploadPolicy {
  stripExif: boolean;
  allowOnlyPublicImages: boolean;
  allowedMimeTypes: string[];
  allowedFormatLabels: string[];
  maxSize: string | null;
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
  allowedMimeTypes: globalSettings?.image?.allowedMimeTypes ?? [],
  allowedFormatLabels: globalSettings?.image?.allowedFormatLabels ?? [],
  maxSize: globalSettings?.image?.maxSize ?? null,
  defaultVisibility: userPreferences?.['image.defaultVisibility'] ?? null,
});
