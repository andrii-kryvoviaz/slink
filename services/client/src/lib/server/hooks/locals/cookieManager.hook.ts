import { env } from '$env/dynamic/private';

import { CookieManager } from '@slink/lib/auth/CookieManager';

import { defineHook } from '../define';

export default defineHook({
  init: (event) => {
    const requireSsl = env.REQUIRE_SSL?.toLowerCase() === 'true' || false;
    event.locals.cookieManager = new CookieManager(requireSsl);
  },
});
