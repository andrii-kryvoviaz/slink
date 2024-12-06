import { ApiClient } from '@slink/api/Client';

import type { PageServerLoad } from './$types';

export const load: PageServerLoad = async ({ parent, locals }) => {
  await parent();

  const { user } = locals;

  let { meta, data: items } = await ApiClient.user.getUsers(1, { limit: 20 });

  return {
    user,
    items,
    meta,
  };
};
