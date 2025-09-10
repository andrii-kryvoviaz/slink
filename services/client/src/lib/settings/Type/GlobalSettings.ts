import type { AccessSettings } from '@slink/lib/settings/Type/AccessSettings';
import type { DemoSettings } from '@slink/lib/settings/Type/DemoSettings';
import type { ImageSettings } from '@slink/lib/settings/Type/ImageSettings';
import type { StorageSettings } from '@slink/lib/settings/Type/StorageSettings';
import type { UserSettings } from '@slink/lib/settings/Type/UserSettings';

export type GlobalSettings = {
  storage: StorageSettings;
  user: UserSettings;
  image: ImageSettings;
  access: AccessSettings;
  demo: DemoSettings;
};

export type SettingCategory = keyof GlobalSettings;
export type SettingCategoryData = GlobalSettings[keyof GlobalSettings];
