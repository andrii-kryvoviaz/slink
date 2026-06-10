import { defineHook } from '../define';

export default defineHook({
  init: (event) => {
    event.locals.locale = event.cookies.get('settings.locale') || 'en';
  },
});
