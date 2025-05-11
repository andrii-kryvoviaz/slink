import type { LayoutServerLoad } from './$types';

export const load: LayoutServerLoad = async ({ locals, request }) => {
  const { settings, user } = locals;

  const userAgent = request.headers.get('user-agent') || '';

  return {
    settings,
    user,
    userAgent,
  };
};
