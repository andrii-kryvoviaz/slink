import type { HttpClient } from '../HttpClient';

export class SettingsApi {
  constructor(private http: HttpClient) {}

  async getSettings() {
    return this.http.request('GET', '/api/settings/global');
  }

  async updateSettings(category: string, settings: object) {
    return this.http.request('POST', '/api/settings', { category, settings });
  }
}
