import type { LayoutServerLoad } from './$types';

export const load: LayoutServerLoad = async ({ locals }) => {
  const { settings, user } = locals;

  return {
    settings,
    user,
  };
};
