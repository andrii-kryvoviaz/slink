import { describe, expect, it } from 'vitest';

import { AssetCache } from './AssetCache.js';

const base = 'https://slink.test/service-worker.js';
const files = [
  { path: '_app/immutable/entry/start.abc.js' },
  { path: '_app/immutable/assets/app.def.css' },
];
const cache = new AssetCache('v2', files, base);

type RequestCase = {
  name: string;
  url: string;
  init?: ConstructorParameters<typeof Request>[1];
};

const servedCases: RequestCase[] = [
  {
    name: 'immutable JS entry',
    url: 'https://slink.test/_app/immutable/entry/start.abc.js',
  },
  {
    name: 'immutable CSS',
    url: 'https://slink.test/_app/immutable/assets/app.def.css',
  },
  {
    name: 'precached path with a query string',
    url: 'https://slink.test/_app/immutable/entry/start.abc.js?v=1',
  },
];

const passThroughCases: RequestCase[] = [
  {
    name: 'POST to precached path',
    url: 'https://slink.test/_app/immutable/entry/start.abc.js',
    init: { method: 'POST' },
  },
  {
    name: 'HEAD on precached path',
    url: 'https://slink.test/_app/immutable/entry/start.abc.js',
    init: { method: 'HEAD' },
  },
  {
    name: 'precached path on another origin',
    url: 'https://cdn.example/_app/immutable/entry/start.abc.js',
  },
  {
    name: 'scheme differs',
    url: 'http://slink.test/_app/immutable/entry/start.abc.js',
  },
  {
    name: 'port differs',
    url: 'https://slink.test:8443/_app/immutable/entry/start.abc.js',
  },
  {
    name: 'subdomain differs',
    url: 'https://www.slink.test/_app/immutable/entry/start.abc.js',
  },
  {
    name: 'Range request on precached path',
    url: 'https://slink.test/_app/immutable/entry/start.abc.js',
    init: { headers: { Range: 'bytes=0-10' } },
  },
  {
    name: 'lowercase open Range request on precached path',
    url: 'https://slink.test/_app/immutable/entry/start.abc.js',
    init: { headers: { range: 'bytes=0-' } },
  },
  { name: 'api list', url: 'https://slink.test/api/images?limit=10' },
  {
    name: 'chunked upload chunk',
    url: 'https://slink.test/api/upload/chunked/u1/0',
    init: { method: 'PUT' },
  },
  {
    name: 'chunked upload status',
    url: 'https://slink.test/api/upload/chunked/u1',
  },
  {
    name: 'mercure stream',
    url: 'https://slink.test/sse?topic=public-feed',
  },
  { name: 'page navigation path', url: 'https://slink.test/explore' },
  {
    name: 'page data',
    url: 'https://slink.test/explore/__data.json?x-sveltekit-invalidated=01',
  },
  {
    name: 'kit version probe',
    url: 'https://slink.test/_app/version.json',
  },
  {
    name: 'previous build chunk',
    url: 'https://slink.test/_app/immutable/nodes/9.old.js',
  },
  {
    name: 'static manifest',
    url: 'https://slink.test/manifest.webmanifest',
  },
  { name: 'image route', url: 'https://slink.test/image/abc.png' },
  {
    name: 'directory prefix only',
    url: 'https://slink.test/_app/immutable/entry/',
  },
  {
    name: 'precached path with suffix',
    url: 'https://slink.test/_app/immutable/entry/start.abc.js/extra',
  },
  {
    name: 'bare path without the _app directory',
    url: 'https://slink.test/start.abc.js',
  },
  {
    name: 'worker script itself',
    url: 'https://slink.test/service-worker.js',
  },
];

const foreignCacheNames = [
  'slink-assets-v1',
  'slink-assets-v2-old',
  'slink-assets-',
  'SLINK-ASSETS-V2',
  '',
  'workbox-precache-v2-https://slink.test/',
  'workbox-runtime-https://slink.test/',
  'api-cache',
  'google-fonts-cache',
];

describe('serves', () => {
  it.each(servedCases)('serves $name', ({ url, init }) => {
    expect(cache.serves(new Request(url, init))).toBe(true);
  });

  it.each(passThroughCases)('passes through $name', ({ url, init }) => {
    expect(cache.serves(new Request(url, init))).toBe(false);
  });

  it('serves nothing when the manifest is empty', () => {
    const empty = new AssetCache('v2', [], base);

    expect(
      empty.serves(
        new Request('https://slink.test/_app/immutable/entry/start.abc.js'),
      ),
    ).toBe(false);
  });

  it('resolves paths against the base origin', () => {
    const other = new AssetCache(
      'v2',
      files,
      'https://other.test/service-worker.js',
    );

    expect(
      other.serves(
        new Request('https://other.test/_app/immutable/entry/start.abc.js'),
      ),
    ).toBe(true);
    expect(
      other.serves(
        new Request('https://slink.test/_app/immutable/entry/start.abc.js'),
      ),
    ).toBe(false);
  });
});

describe('owns', () => {
  it('owns the cache of its own version', () => {
    expect(cache.owns('slink-assets-v2')).toBe(true);
  });

  it.each(foreignCacheNames)('does not own %j', (name) => {
    expect(cache.owns(name)).toBe(false);
  });
});
