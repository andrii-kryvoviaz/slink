export abstract class AbstractResource {
  private _baseUrl: string;

  constructor(baseUrl: string) {
    this._baseUrl = baseUrl;
  }

  protected get(path: string, options?: RequestInit) {
    return fetch(`${this._baseUrl}${path}`, {
      ...options,
      method: 'GET',
    });
  }

  protected post(path: string, options?: RequestInit) {
    return fetch(`${this._baseUrl}${path}`, {
      ...options,
      method: 'POST',
    });
  }

  protected put(path: string, options?: RequestInit) {
    return fetch(`${this._baseUrl}${path}`, {
      ...options,
      method: 'PUT',
    });
  }

  protected delete(path: string, options?: RequestInit) {
    return fetch(`${this._baseUrl}${path}`, {
      ...options,
      method: 'DELETE',
    });
  }

  protected patch(path: string, options?: RequestInit) {
    return fetch(`${this._baseUrl}${path}`, {
      ...options,
      method: 'PATCH',
    });
  }
}
