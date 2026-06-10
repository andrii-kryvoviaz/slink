import { defineHook } from '../define';

export default defineHook({
  init: async (event) => {
    try {
      event.locals.globalSettings =
        await event.locals.api.setting.getPublicSettings();
    } catch {
      event.locals.globalSettings = null;
    }
  },
});
