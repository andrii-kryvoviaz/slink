import { defineHook } from '../define';

export default defineHook({
  init: async (event) => {
    event.locals.userPreferences = null;

    if (!event.locals.user) {
      return;
    }

    try {
      event.locals.userPreferences =
        await event.locals.api.user.getPreferences();
    } catch {
      console.warn(
        `Failed to fetch user preferences for user ${event.locals.user.id}`,
      );
    }
  },
});
