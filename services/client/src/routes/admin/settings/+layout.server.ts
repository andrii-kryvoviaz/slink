import type { LayoutServerLoad } from './$types';

export const load: LayoutServerLoad = async ({ parent, locals }) => {
  await parent();

  const api = locals.api;

  const [settings, defaultSettings] = await Promise.all([
    api.setting.getGlobalSettings(),
    api.setting.getSettings({ provider: 'default' }),
  ]);

  return {
    settings,
    defaultSettings,
  };
};
