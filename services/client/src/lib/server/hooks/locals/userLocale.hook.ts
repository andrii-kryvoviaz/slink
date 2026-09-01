import { resolveLocale } from '@slink/lib/settings/Settings.enums';

import { defineHook } from '../define';

export default defineHook({
  init: (event) => {
    if (!event.locals.user) {
      return;
    }

    const userLocale = event.locals.userPreferences?.['display.language'];

    event.locals.cookies.settings.set('locale', resolveLocale(userLocale));
  },
});
