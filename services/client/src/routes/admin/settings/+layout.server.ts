import { ApiClient } from '@slink/api/Client';

import type { LayoutServerLoad } from './$types';

export const load: LayoutServerLoad = async ({ parent, fetch }) => {
  await parent();

  const api = ApiClient.use(fetch);

  const [settings, defaultSettings] = await Promise.all([
    api.setting.getGlobalSettings(),
    api.setting.getSettings({ provider: 'default' }),
  ]);

  return {
    settings,
    defaultSettings,
  };
};
