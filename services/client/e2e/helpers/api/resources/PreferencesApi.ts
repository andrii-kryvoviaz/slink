import type { HttpClient } from '../HttpClient';

export class PreferencesApi {
  constructor(private http: HttpClient) {}

  async updatePreferences(preferences: Record<string, unknown>) {
    return this.http.request('PATCH', '/api/user/preferences', preferences);
  }
}
