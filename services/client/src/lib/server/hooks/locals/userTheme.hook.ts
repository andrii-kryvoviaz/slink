import { resolveTheme } from '@slink/lib/settings/Settings.enums';

import { defineHook } from '../define';

export default defineHook({
  init: (event) => {
    const userTheme = event.locals.userPreferences?.['display.theme'];
    if (userTheme) {
      event.cookies.set('settings.theme', resolveTheme(userTheme), {
        path: '/',
        maxAge: 31536000,
        httpOnly: false,
        secure: false,
        sameSite: 'strict',
      });
    }
  },
});
