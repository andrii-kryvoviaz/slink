import type { Client } from '@slink/api/Client';
import type { RequestOptions } from '@slink/api/Type/RequestOptions';

export abstract class AbstractResource {
  constructor(private readonly _client: Client) {}

  private request<T>(
    method: string,
    path: string,
    options?: RequestOptions,
  ): Promise<T> {
    return this._client.fetch(path, {
      ...options,
      method,
    }) as Promise<T>;
  }

  protected get<T = unknown>(
    path: string,
    options?: RequestOptions,
  ): Promise<T> {
    return this.request('GET', path, options);
  }

  protected post<T = unknown>(
    path: string,
    options?: RequestOptions,
  ): Promise<T> {
    return this.request('POST', path, options);
  }

  protected put<T = unknown>(
    path: string,
    options?: RequestOptions,
  ): Promise<T> {
    return this.request('PUT', path, options);
  }

  protected delete<T = unknown>(
    path: string,
    options?: RequestOptions,
  ): Promise<T> {
    return this.request('DELETE', path, options);
  }

  protected patch<T = unknown>(
    path: string,
    options?: RequestOptions,
  ): Promise<T> {
    return this.request('PATCH', path, options);
  }
}
