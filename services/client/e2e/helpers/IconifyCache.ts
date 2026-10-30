import type { BrowserContext, Route } from '@playwright/test';

type CachedResponse = {
  status: number;
  headers: Record<string, string>;
  body: Buffer;
};

const ICONIFY_API =
  /^https:\/\/api\.(iconify\.design|simplesvg\.com|unisvg\.com)\//;

const DROPPED_HEADERS = new Set([
  'content-encoding',
  'content-length',
  'transfer-encoding',
]);

export class IconifyCache {
  private static readonly _responses = new Map<
    string,
    Promise<CachedResponse>
  >();

  static async attach(context: BrowserContext): Promise<void> {
    await context.route(ICONIFY_API, (route) => IconifyCache._serve(route));
  }

  private static async _serve(route: Route): Promise<void> {
    try {
      await route.fulfill(await IconifyCache._resolve(route));
    } catch {
      await route.abort().catch(() => undefined);
    }
  }

  private static _resolve(route: Route): Promise<CachedResponse> {
    const url = route.request().url();
    const cached = IconifyCache._responses.get(url);

    if (cached) {
      return cached;
    }

    const pending = IconifyCache._fetch(route);
    IconifyCache._responses.set(url, pending);
    pending.catch(() => IconifyCache._responses.delete(url));

    return pending;
  }

  private static async _fetch(route: Route): Promise<CachedResponse> {
    const response = await route.fetch();
    const headers = Object.fromEntries(
      Object.entries(response.headers()).filter(
        ([name]) => !DROPPED_HEADERS.has(name),
      ),
    );

    return { status: response.status(), headers, body: await response.body() };
  }
}
