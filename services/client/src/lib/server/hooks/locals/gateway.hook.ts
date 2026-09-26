import { Gateway } from '@slink/lib/auth/Gateway';

import { defineHook } from '../define';

export default defineHook({
  init: (event) => {
    event.locals.gateway = new Gateway(event.locals);
  },
});
