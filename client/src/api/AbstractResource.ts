import { error } from '@sveltejs/kit';

import { ValidationException } from '@slink/api/Exceptions/ValidationException';
import type { ViolationResponse } from '@slink/api/Response/Error/ViolationResponse';

export abstract class AbstractResource {
  private _fetch: Function | null = null;

  constructor(private readonly _baseUrl: string) {}

  public useFetch(fetchFn: Function) {
    this._fetch = fetchFn;
  }

  private async baseRequest(path: string, options?: RequestInit) {
    if (!this._fetch) {
      this._fetch = fetch;
      console.warn(
        'API client is not initialized with fetch function, falling back to global fetch function. To utilize SSR, add `ApiClient.use(fetch)` to your `load` function.'
      );
    }

    const response = await this._fetch(`${this._baseUrl}${path}`, options);

    if (response.status === 204) {
      return;
    }

    if (response.ok && response.status < 400) {
      const parsed = await response.json();
      return parsed?.data ?? parsed;
    }

    if (response.status === 404) {
      error(404, {
        message: 'Not found',
      });
    }

    if (response.status === 422) {
      const data = (await response.json()).error as ViolationResponse;
      throw new ValidationException(data);
    }
  }

  protected get(path: string, options?: RequestInit) {
    return this.baseRequest(path, {
      ...options,
      method: 'GET',
    });
  }

  protected post(path: string, options?: RequestInit) {
    return this.baseRequest(path, {
      ...options,
      method: 'POST',
    });
  }

  protected put(path: string, options?: RequestInit) {
    return this.baseRequest(path, {
      ...options,
      method: 'PUT',
    });
  }

  protected delete(path: string, options?: RequestInit) {
    return this.baseRequest(path, {
      ...options,
      method: 'DELETE',
    });
  }

  protected patch(path: string, options?: RequestInit) {
    return this.baseRequest(path, {
      ...options,
      method: 'PATCH',
    });
  }
}
