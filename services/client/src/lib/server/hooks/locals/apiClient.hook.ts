import { createApiClient } from '@slink/api/Client';

import { defineHook } from '../define';

export default defineHook({
  init: (event) => {
    event.locals.api = createApiClient(event.fetch);
  },
});
