import type { PageServerLoad } from './$types';

export const load: PageServerLoad = async ({ parent, params, locals }) => {
  const { user } = await parent();

  return {
    user,
    collectionId: params.id,
    globalSettings: locals.globalSettings,
  };
};
