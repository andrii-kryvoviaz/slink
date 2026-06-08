import type { PageServerLoad } from './$types';

export const load: PageServerLoad = async ({ locals }) => {
  const { user, globalSettings, userPreferences } = locals;

  const allowOnlyPublicImages =
    globalSettings?.image?.allowOnlyPublicImages ?? false;
  const defaultVisibility =
    userPreferences?.['image.defaultVisibility'] ?? null;

  return {
    user,
    defaultVisibility,
    allowOnlyPublicImages,
  };
};
