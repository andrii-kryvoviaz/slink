import type { PageServerLoad } from './$types';

export const load: PageServerLoad = async ({ parent, locals }) => {
  await parent();

  const { user } = locals;

  return {
    user,
  };
};
