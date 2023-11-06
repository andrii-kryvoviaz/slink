import type { ViolationResponse } from './Response/Error/ViolationResponse';
import { ValidationException } from './Exceptions/ValidationException';

export abstract class AbstractResource {
  private _baseUrl: string;

  constructor(baseUrl: string) {
    this._baseUrl = baseUrl;
  }

  private async baseRequest(path: string, options?: RequestInit) {
    const response = await fetch(`${this._baseUrl}${path}`, options);

    if (response.ok && response.status < 400) {
      return response.json();
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
