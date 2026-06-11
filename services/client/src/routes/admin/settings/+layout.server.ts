import type { LayoutServerLoad } from './$types';

export const load: LayoutServerLoad = async ({ parent, locals, depends }) => {
  await parent();
  depends('app:settings');

  const api = locals.api;

  const [adminSettings, defaultSettings] = await Promise.all([
    api.setting.getGlobalSettings(),
    api.setting.getSettings({ provider: 'default' }),
  ]);

  return {
    adminSettings,
    defaultSettings,
  };
};
