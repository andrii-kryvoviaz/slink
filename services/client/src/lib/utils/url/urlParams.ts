export interface UrlParamConfig {
  replaceState?: boolean;
  noScroll?: boolean;
}

export class UrlParamManager {
  private readonly currentUrl: URL;
  private readonly params: URLSearchParams;

  constructor(url: string | URL) {
    if (typeof url === 'string' && !url.startsWith('http')) {
      this.currentUrl = new URL(url, window.location.origin);
    } else {
      this.currentUrl = new URL(url);
    }
    this.params = new URLSearchParams(this.currentUrl.search);
  }

  static fromPageUrl(pageUrl: URL): UrlParamManager {
    return new UrlParamManager(pageUrl);
  }

  set(key: string, value: string | number | boolean): this {
    this.params.set(key, String(value));
    return this;
  }

  setArray(key: string, values: string[]): this {
    this.params.delete(key);
    if (values.length > 0) {
      this.params.set(key, values.join(','));
    }
    return this;
  }

  get(key: string): string | null {
    return this.params.get(key);
  }

  getArray(key: string): string[] {
    const value = this.params.get(key);
    return value ? value.split(',').filter(Boolean) : [];
  }

  getBoolean(key: string): boolean {
    return this.params.get(key) === 'true';
  }

  delete(key: string): this {
    this.params.delete(key);
    return this;
  }

  clear(): this {
    this.params.forEach((_, key) => this.params.delete(key));
    return this;
  }

  has(key: string): boolean {
    return this.params.has(key);
  }

  buildUrl(): string {
    const paramString = this.params.toString();
    return paramString
      ? `${this.currentUrl.pathname}?${paramString}`
      : this.currentUrl.pathname;
  }

  toSearchParams(): URLSearchParams {
    return new URLSearchParams(this.params);
  }
}

export const urlParamUtils = {
  create: (url: string | URL) => new UrlParamManager(url),
  fromPage: (pageUrl: URL) => UrlParamManager.fromPageUrl(pageUrl),
};
