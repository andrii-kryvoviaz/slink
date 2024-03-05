import type { PageServerLoad } from './$types';

export const load: PageServerLoad = async ({ parent }) => {
  await parent();
};
