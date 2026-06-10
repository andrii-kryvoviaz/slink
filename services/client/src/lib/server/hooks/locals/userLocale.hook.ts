import { defineHook } from '../define';

export default defineHook({
  init: (event) => {
    const userLocale = event.locals.userPreferences?.['display.language'];
    if (userLocale) {
      event.locals.locale = userLocale;
      if (userLocale !== event.cookies.get('settings.locale')) {
        event.cookies.set('settings.locale', userLocale, {
          path: '/',
          maxAge: 31536000,
        });
      }
    }
  },
});
