export class AssetCache {
  private readonly _name: string;
  private readonly _origin: string;
  private readonly _paths: ReadonlySet<string>;

  constructor(
    version: string,
    files: ReadonlyArray<{ path: string }>,
    base: string,
  ) {
    this._name = `slink-assets-${version}`;
    this._origin = new URL(base).origin;
    this._paths = new Set(
      files.map((file) => new URL(file.path, base).pathname),
    );
  }

  serves(request: Request): boolean {
    if (request.method !== 'GET') {
      return false;
    }
    if (request.headers.has('range')) {
      return false;
    }
    const url = new URL(request.url);
    return url.origin === this._origin && this._paths.has(url.pathname);
  }

  owns(cacheName: string): boolean {
    return cacheName === this._name;
  }

  async precache(): Promise<void> {
    const cache = await caches.open(this._name);
    await cache.addAll([...this._paths]);
  }

  async purge(): Promise<void> {
    const names = await caches.keys();
    await Promise.all(
      names
        .filter((name) => !this.owns(name))
        .map((name) => caches.delete(name)),
    );
  }

  async respond(request: Request): Promise<Response> {
    const cached = await caches.match(request, { cacheName: this._name });
    return cached ?? fetch(request);
  }
}
