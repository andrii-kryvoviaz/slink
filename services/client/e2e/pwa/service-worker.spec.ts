import { expect, test } from '../fixtures/auth.fixture';
import { ServiceWorkerProbe } from '../helpers/ServiceWorkerProbe';

const ASSET_CACHE = /^slink-assets-/;

test.describe('Service worker', { tag: '@anonymous' }, () => {
  test('registers the worker and takes control of the page', async ({
    page,
    context,
  }) => {
    const probe = new ServiceWorkerProbe(page);
    const registered = context.waitForEvent('serviceworker');
    await page.goto('/explore');
    const worker = await registered;
    expect(worker.url()).toMatch(/\/service-worker\.js$/);
    await probe.waitUntilControlling();
  });

  test('serves precached immutable assets while offline', async ({
    page,
    context,
  }) => {
    const probe = new ServiceWorkerProbe(page);
    await page.goto('/explore');
    await probe.waitUntilControlling();
    const asset = (await probe.cachedUrls()).find((url) => {
      const { pathname } = new URL(url);
      return (
        pathname.startsWith('/_app/immutable/') && pathname.endsWith('.js')
      );
    });
    if (!asset) {
      throw new Error('no precached immutable script');
    }
    await context.setOffline(true);
    const served = page.waitForResponse(asset);
    await page.evaluate((url) => fetch(url).then(() => undefined), asset);
    const response = await served;
    expect(response.status()).toBe(200);
    expect(response.fromServiceWorker()).toBe(true);
  });

  test('does not serve navigations while offline', async ({
    page,
    context,
  }) => {
    const probe = new ServiceWorkerProbe(page);
    await page.goto('/explore');
    await probe.waitUntilControlling();
    await context.setOffline(true);
    await expect(page.reload()).rejects.toThrow(/ERR_INTERNET_DISCONNECTED/);
  });

  test('purges caches left by the legacy workbox worker', async ({ page }) => {
    const probe = new ServiceWorkerProbe(page);
    await page.goto('/manifest.webmanifest');
    const legacy = `workbox-precache-v2-${new URL(page.url()).origin}/`;
    await probe.seedCache(legacy);
    await probe.seedCache('api-cache');
    expect(await probe.cacheNames()).toEqual(
      expect.arrayContaining([legacy, 'api-cache']),
    );
    await page.goto('/explore');
    await probe.waitUntilControlling();
    await expect
      .poll(() => probe.cacheNames())
      .toEqual([expect.stringMatching(ASSET_CACHE)]);
  });

  test('serves the web app manifest', async ({ page }) => {
    const response = await page.request.get('/manifest.webmanifest');
    expect(response.ok()).toBe(true);
    expect(response.headers()['content-type']).toContain('manifest+json');
    expect((await response.json()).name).toBe('Slink');
    await page.goto('/explore');
    const href = await page.evaluate(
      () =>
        document.querySelector<HTMLLinkElement>('link[rel="manifest"]')?.href,
    );
    expect(href).toBe(new URL('/manifest.webmanifest', page.url()).href);
  });
});

test.describe('Service worker for signed-in users', () => {
  test('never serves or caches API responses', async ({ page, api }) => {
    const probe = new ServiceWorkerProbe(page);
    await api.content.uploadImage({ isPublic: true });
    await page.goto('/explore');
    await probe.waitUntilControlling();
    const listing = page.waitForResponse(
      (response) =>
        response.request().method() === 'GET' &&
        new URL(response.url()).pathname === '/api/images',
    );
    await page.reload();
    const response = await listing;
    expect(response.fromServiceWorker()).toBe(false);
    expect(
      (await probe.cachedUrls()).filter((url) => url.includes('/api/')),
    ).toEqual([]);
    expect(await probe.cacheNames()).toEqual([
      expect.stringMatching(ASSET_CACHE),
    ]);
  });
});
