import { AbstractResource } from '@slink/api/AbstractResource';
import type { SettingRequest } from '@slink/api/Request/SettingRequest';
import type { EmptyResponse, SettingsResponse } from '@slink/api/Response';

import type {
  SettingCategory,
  SettingCategoryData,
} from '@slink/lib/settings/Type/GlobalSettings';

export class SettingResource extends AbstractResource {
  public async getGlobalSettings(): Promise<SettingsResponse> {
    return this.get('/settings/global');
  }

  public async getPublicSettings(): Promise<SettingsResponse> {
    return this.get('/settings/public');
  }

  public async getSettings(
    request: SettingRequest,
  ): Promise<Partial<SettingsResponse>> {
    return this.get('/settings', { query: request });
  }

  public async updateSettings(
    category: SettingCategory,
    settings: Partial<SettingCategoryData>,
  ): Promise<EmptyResponse> {
    return this.post('/settings', {
      json: { category, settings },
    });
  }
}
