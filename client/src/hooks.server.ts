import { env } from '$env/dynamic/private';
import { Theme, setCookieSettingsOnLocals } from '@slink/lib/settings';
import type { Handle } from '@sveltejs/kit';
import { sequence } from '@sveltejs/kit/hooks';

import { ApiConnector } from '@slink/api/ApiConnector';

const injectApiHandling: Handle = ApiConnector({
  urlPrefix: env.API_PREFIX,
  baseUrl: env.API_URL,
  registeredPaths: env.PROXY_PREFIXES.split(';'),
});

const filterResponseHeaders: Handle = async ({ event, resolve }) => {
  const headers = ['location', 'content-type'];
  return resolve(event, {
    filterSerializedResponseHeaders: (name) => {
      return name.startsWith('x-') || headers.includes(name);
    },
  });
};

const applyClientTheme: Handle = async ({ event, resolve }) => {
  const theme = event.cookies.get('settings.theme') || Theme.DARK;

  return resolve(event, {
    transformPageChunk: ({ html }) => html.replace('%app.theme%', theme),
  });
};

export const handle = sequence(
  filterResponseHeaders,
  injectApiHandling,
  setCookieSettingsOnLocals,
  applyClientTheme
);
