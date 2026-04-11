import { env } from '$env/dynamic/private';
import { json } from '@sveltejs/kit';
import type { Handle } from '@sveltejs/kit';
import { sequence } from '@sveltejs/kit/hooks';
import { loadLocales, runWithLocale } from 'wuchale/load-utils/server';

import { ApiProxy } from '@slink/api/ApiProxy';
import { createApiClient } from '@slink/api/Client';

import { CookieManager } from '@slink/lib/auth/CookieManager';
import { createFlash } from '@slink/lib/flash/flash';
import { Theme, setCookieSettingsOnLocals } from '@slink/lib/settings/server';

import { locales } from './locales/data.js';
import * as js from './locales/js.loader.server.js';
import * as main from './locales/main.loader.server.svelte.js';

await loadLocales(main.key, main.loadIDs, main.loadCatalog, locales);
await loadLocales(js.key, js.loadIDs, js.loadCatalog, locales);

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
  urlPrefix: '/api',
  baseUrl: env.API_URL || 'http://localhost:8080',
  registeredPaths: ['/api', '/image'],
});

const filterResponseHeaders: Handle = async ({ event, resolve }) => {
  const headers = ['location', 'content-type'];
  return resolve(event, {
    filterSerializedResponseHeaders: (name) => {
      return name.startsWith('x-') || headers.includes(name);
    },
  });
};

const initializeLocale: Handle = async ({ event, resolve }) => {
  event.locals.locale = event.cookies.get('settings.locale') || 'en';
  return resolve(event);
};

const applyClientLocale: Handle = async ({ event, resolve }) => {
  const locale = event.locals.locale;

  return await runWithLocale(locale, () =>
    resolve(event, {
      transformPageChunk: ({ html }) => html.replace('%app.locale%', locale),
    }),
  );
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
  initializeLocale,
  initializeFlash,
  initializeApiClient,
  injectApiHandling,
  setGlobalSettingsOnLocals,
  setUserPreferencesOnLocals,
  setCookieSettingsOnLocals,
  applyClientTheme,
  applyClientLocale,
  handleLinkHeaderPreloading,
);
