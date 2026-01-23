import type { PageServerLoad } from './$types';

export const load: PageServerLoad = async ({ parent, params }) => {
  const { user } = await parent();

  return {
    user,
    collectionId: params.id,
  };
};
