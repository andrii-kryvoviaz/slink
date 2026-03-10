import { env } from '$env/dynamic/private';
import { json } from '@sveltejs/kit';
import type { Handle } from '@sveltejs/kit';
import { sequence } from '@sveltejs/kit/hooks';

import { ApiProxy } from '@slink/api/ApiProxy';
import { createApiClient } from '@slink/api/Client';

import { CookieManager } from '@slink/lib/auth/CookieManager';
import { createFlash } from '@slink/lib/flash/flash';
import { Theme, setCookieSettingsOnLocals } from '@slink/lib/settings/server';

const handleWellKnownRequests: Handle = async ({ event, resolve }) => {
  const { pathname } = event.url;

  if (pathname.startsWith('/.well-known/')) {
    if (pathname.endsWith('.json')) {
      return json({});
    }

    return new Response('Not Found', { status: 404 });
  }

  return resolve(event);
};

const injectApiHandling: Handle = ApiProxy({
  urlPrefix: env.API_PREFIX || '/api',
  baseUrl: env.API_URL || 'http://localhost:8080',
  registeredPaths: env.PROXY_PREFIXES?.split(';') || [],
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

const initializeCookieManager: Handle = async ({ event, resolve }) => {
  const requireSsl = env.REQUIRE_SSL?.toLowerCase() === 'true' || false;
  event.locals.cookieManager = new CookieManager(requireSsl);
  return resolve(event);
};

const initializeFlash: Handle = async ({ event, resolve }) => {
  event.locals.flash = createFlash(event.locals.cookieManager, event.cookies);
  return resolve(event);
};

const initializeApiClient: Handle = async ({ event, resolve }) => {
  event.locals.api = createApiClient(event.fetch);
  return resolve(event);
};

const setGlobalSettingsOnLocals: Handle = async ({ event, resolve }) => {
  try {
    event.locals.globalSettings =
      await event.locals.api.setting.getPublicSettings();
  } catch {
    event.locals.globalSettings = null;
  }

  return resolve(event);
};

const setUserPreferencesOnLocals: Handle = async ({ event, resolve }) => {
  event.locals.userPreferences = null;

  if (!event.locals.user) {
    return resolve(event);
  }

  try {
    event.locals.userPreferences = await event.locals.api.user.getPreferences();
  } catch {
    console.warn(
      `Failed to fetch user preferences for user ${event.locals.user.id}`,
    );
  }

  return resolve(event);
};

const handleLinkHeaderPreloading: Handle = async ({ event, resolve }) => {
  const response = await resolve(event);

  if (!response.headers.has('link')) {
    return response;
  }

  const linkHeader = response.headers.get('link');
  if (linkHeader && linkHeader.length >= 4096) {
    response.headers.delete('link');
  }

  return response;
};

export const handle = sequence(
  handleWellKnownRequests,
  filterResponseHeaders,
  initializeCookieManager,
  initializeFlash,
  initializeApiClient,
  injectApiHandling,
  setGlobalSettingsOnLocals,
  setUserPreferencesOnLocals,
  setCookieSettingsOnLocals,
  applyClientTheme,
  handleLinkHeaderPreloading,
);
