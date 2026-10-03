import { AssetCache } from '#lib/services/AssetCache/index.js';
import { version } from '$app/env';
import { immutable } from '$app/manifest';
import { self } from '$app/service-worker';

const assets = new AssetCache(version, immutable, self.location.href);

self.addEventListener('install', (event) => {
  event.waitUntil(assets.precache());
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(assets.purge().then(() => self.clients.claim()));
});

self.addEventListener('fetch', (event) => {
  if (!assets.serves(event.request)) {
    return;
  }
  event.respondWith(assets.respond(event.request));
});
