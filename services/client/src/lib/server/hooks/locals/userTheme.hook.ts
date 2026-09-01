import { resolveTheme } from '@slink/lib/settings/Settings.enums';

import { defineHook } from '../define';

export default defineHook({
  init: (event) => {
    if (!event.locals.user) {
      return;
    }

    const userTheme = event.locals.userPreferences?.['display.theme'];

    event.locals.cookies.settings.set('theme', resolveTheme(userTheme));
  },
});
