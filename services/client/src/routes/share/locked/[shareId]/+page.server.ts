import type { PageServerLoad } from './$types';

export const load: PageServerLoad = ({ params }) => {
  return { shareId: params.shareId };
};
