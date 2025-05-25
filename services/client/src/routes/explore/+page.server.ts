import type { PageServerLoad } from './$types';

export const load: PageServerLoad = async ({ parent, locals }) => {
  await parent();

  return {
    user: locals.user,
  };
};
