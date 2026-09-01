import { env } from '$env/dynamic/private';

import { CookieManager } from '@slink/lib/auth/CookieManager';
import { withScopes } from '@slink/lib/auth/CookiePolicy';
import { settingsPolicy } from '@slink/lib/settings/SettingsPolicy';

import { defineHook } from '../define';

export const cookiePolicies = {
  settings: settingsPolicy,
} as const;

export default defineHook({
  init: (event) => {
    const requireSsl = env.REQUIRE_SSL?.toLowerCase() === 'true' || false;

    event.locals.cookies = withScopes(
      new CookieManager(requireSsl, event.cookies),
      cookiePolicies,
    );
  },
});
