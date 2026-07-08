import type { ImageSettings } from '@slink/lib/settings/Type/ImageSettings';

import type { LayoutServerLoad } from './$types';

const ensureAllowedFormats = (image?: Partial<ImageSettings>): void => {
  if (!image) {
    return;
  }

  image.allowedFormats ??= -1;
};

export const load: LayoutServerLoad = async ({ parent, locals, depends }) => {
  await parent();
  depends('app:settings');

  const api = locals.api;

  const [adminSettings, defaultSettings] = await Promise.all([
    api.setting.getGlobalSettings(),
    api.setting.getSettings({ provider: 'default' }),
  ]);

  ensureAllowedFormats(adminSettings.image);
  ensureAllowedFormats(defaultSettings.image);

  return {
    adminSettings,
    defaultSettings,
  };
};
