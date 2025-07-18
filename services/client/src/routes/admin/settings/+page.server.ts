import { ApiClient } from '@slink/api/Client';

import type { PageServerLoad } from './$types';

export const load: PageServerLoad = async ({ parent, fetch }) => {
  await parent();

  const defaultSettings = await ApiClient.use(fetch).setting.getSettings({
    provider: 'default',
  });

  return {
    defaultSettings,
  };
};
