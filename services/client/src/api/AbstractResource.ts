import type { Client } from '@slink/api/Client';
import type { RequestOptions } from '@slink/api/Type/RequestOptions';

export abstract class AbstractResource {
  constructor(private readonly _client: Client) {}

  protected get(path: string, options?: RequestOptions) {
    return this._client.fetch(path, {
      ...options,
      method: 'GET',
    });
  }

  protected post(path: string, options?: RequestOptions) {
    return this._client.fetch(path, {
      ...options,
      method: 'POST',
    });
  }

  protected put(path: string, options?: RequestOptions) {
    return this._client.fetch(path, {
      ...options,
      method: 'PUT',
    });
  }

  protected delete(path: string, options?: RequestOptions) {
    return this._client.fetch(path, {
      ...options,
      method: 'DELETE',
    });
  }

  protected patch(path: string, options?: RequestOptions) {
    return this._client.fetch(path, {
      ...options,
      method: 'PATCH',
    });
  }
}
