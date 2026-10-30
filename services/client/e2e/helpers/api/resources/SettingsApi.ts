import type { HttpClient } from '../HttpClient';

export class SettingsApi {
  constructor(private http: HttpClient) {}

  async getSettings() {
    return this.http.request('GET', '/api/settings/global');
  }

  async getPublicSettings() {
    const { data } = await this.http.request('GET', '/api/settings/public');
    return data;
  }

  async getDefaultSettings() {
    return this.http.request('GET', '/api/settings?provider=default');
  }

  async updateSettings(category: string, settings: object) {
    return this.http.request('POST', '/api/settings', { category, settings });
  }
}
