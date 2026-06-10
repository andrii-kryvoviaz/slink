import { resolveUploadPolicy } from '@slink/lib/settings/UploadPolicy';

import { defineHook } from '../define';

export default defineHook({
  init: (event) => {
    event.locals.uploadPolicy = resolveUploadPolicy(
      event.locals.globalSettings,
      event.locals.userPreferences,
    );
  },
});
