import { resolveSettingsCookies } from '@slink/lib/settings/SettingsPolicy';
import { UserSettings } from '@slink/lib/settings/UserSettings.svelte';

import { defineHook } from '../define';

export default defineHook({
  init: (event) => {
    const cookieData = resolveSettingsCookies((name) =>
      event.cookies.get(name),
    );

    event.locals.settings = new UserSettings(cookieData);
  },
});
