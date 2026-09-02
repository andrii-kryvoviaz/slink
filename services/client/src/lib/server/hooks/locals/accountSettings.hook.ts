import {
  type AccountSettingsKey,
  accountSettings,
  accountSettingsPolicy,
} from '@slink/lib/settings/SettingsPolicy';

import { defineHook } from '../define';

export default defineHook({
  init: (event) => {
    if (!event.locals.user) {
      return;
    }

    const scope = event.locals.cookies.use(accountSettingsPolicy);

    for (const [key, { preference, resolve }] of Object.entries(
      accountSettings,
    )) {
      scope.set(
        key as AccountSettingsKey,
        resolve(event.locals.userPreferences?.[preference]),
      );
    }
  },
});
