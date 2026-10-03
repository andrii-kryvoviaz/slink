import { type Page, expect } from '@playwright/test';

const CONTROL_TIMEOUT = 15000;

export class ServiceWorkerProbe {
  constructor(private readonly _page: Page) {}

  async waitUntilControlling(): Promise<void> {
    await expect
      .poll(
        () =>
          this._page.evaluate(
            () => navigator.serviceWorker.controller?.scriptURL,
          ),
        { timeout: CONTROL_TIMEOUT },
      )
      .toMatch(/\/service-worker\.js$/);
  }

  async cacheNames(): Promise<string[]> {
    return this._page.evaluate(() => caches.keys());
  }

  async cachedUrls(): Promise<string[]> {
    return this._page.evaluate(async () => {
      const names = await caches.keys();
      const entries = await Promise.all(
        names.map(async (name) => (await caches.open(name)).keys()),
      );
      return entries.flat().map((request) => request.url);
    });
  }

  async seedCache(name: string): Promise<void> {
    await this._page.evaluate(async (cacheName) => {
      const cache = await caches.open(cacheName);
      await cache.put('/e2e-seed', new Response('seed'));
    }, name);
  }
}
