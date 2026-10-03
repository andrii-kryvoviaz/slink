import { REQUIRE_SSL } from '$app/env/private';

import { CookieManager } from '@slink/lib/auth/CookieManager';

import { defineHook } from '../define';

export default defineHook({
  init: (event) => {
    const requireSsl = REQUIRE_SSL?.toLowerCase() === 'true' || false;

    event.locals.cookies = new CookieManager(requireSsl, event.cookies);
  },
});
