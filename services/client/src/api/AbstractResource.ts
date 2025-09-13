import type { Client } from '@slink/api/Client';
import type { RequestOptions } from '@slink/api/Type/RequestOptions';

export abstract class AbstractResource {
  constructor(private readonly _client: Client) {}

  protected get<T = unknown>(
    path: string,
    options?: RequestOptions,
  ): Promise<T> {
    return this._client.fetch(path, {
      ...options,
      method: 'GET',
    }) as Promise<T>;
  }

  protected post<T = unknown>(
    path: string,
    options?: RequestOptions,
  ): Promise<T> {
    return this._client.fetch(path, {
      ...options,
      method: 'POST',
    }) as Promise<T>;
  }

  protected put<T = unknown>(
    path: string,
    options?: RequestOptions,
  ): Promise<T> {
    return this._client.fetch(path, {
      ...options,
      method: 'PUT',
    }) as Promise<T>;
  }

  protected delete<T = unknown>(
    path: string,
    options?: RequestOptions,
  ): Promise<T> {
    return this._client.fetch(path, {
      ...options,
      method: 'DELETE',
    }) as Promise<T>;
  }

  protected patch<T = unknown>(
    path: string,
    options?: RequestOptions,
  ): Promise<T> {
    return this._client.fetch(path, {
      ...options,
      method: 'PATCH',
    }) as Promise<T>;
  }
}
