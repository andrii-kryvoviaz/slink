import type { LayoutServerLoad } from './$types';

export const load: LayoutServerLoad = async ({ locals }) => {
  const { settings } = locals;

  return {
    settings,
  };
};
