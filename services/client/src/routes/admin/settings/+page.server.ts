import { ApiClient } from '@slink/api/Client';

import type { PageServerLoad } from './$types';

export const load: PageServerLoad = async ({ parent }) => {
  await parent();

  const defaultSettings = await ApiClient.setting.getSettings({
    provider: 'default',
  });

  return {
    defaultSettings,
  };
};
