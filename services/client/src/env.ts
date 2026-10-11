import { defineEnvVars } from '@sveltejs/kit/env';

const optional = (value: string | undefined) => value;

export const variables = defineEnvVars({
  API_URL: { schema: optional },
  MERCURE_HUB_URL: { schema: optional },
  NODE_ENV: { schema: optional },
  REQUIRE_SSL: { schema: optional },
  SLINK_VERSION: { schema: (value: string | undefined) => value || 'dev' },
  SESSION_TTL_SECONDS: { schema: optional },
});
