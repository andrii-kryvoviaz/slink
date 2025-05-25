import type { PageServerLoad } from './$types';

import { ApiClient } from '@slink/api/Client';

export const load: PageServerLoad = async ({ parent }) => {
  await parent();

  const settings = await ApiClient.setting.getGlobalSettings();
  const defaultSettings = await ApiClient.setting.getSettings({
    provider: 'default',
  });

  return {
    settings,
    defaultSettings,
  };
};
