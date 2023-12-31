import type { ViolationResponse } from './Response/Error/ViolationResponse';
import { error } from '@sveltejs/kit';

import { ValidationException } from './Exceptions/ValidationException';

export abstract class AbstractResource {
  private _baseUrl: string;

  private _fetch: Function = fetch;

  constructor(baseUrl: string) {
    this._baseUrl = baseUrl;
  }

  public using(fetchFn: Function) {
    this._fetch = fetchFn;
    return this;
  }

  protected resetFetch() {
    this._fetch = fetch;
  }

  private async baseRequest(path: string, options?: RequestInit) {
    const response = await this._fetch(`${this._baseUrl}${path}`, options);

    this.resetFetch();

    if (response.status === 204) {
      return;
    }

    if (response.ok && response.status < 400) {
      const parsed = await response.json();
      return parsed?.data ?? parsed;
    }

    if (response.status === 404) {
      throw error(404, {
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
