import { AbstractResource } from '@slink/api/AbstractResource';
import type { EmptyResponse } from '@slink/api/Response';

export type ApiKeyResponse = {
  id: string;
  name: string;
  createdAt: string;
  expiresAt?: string;
  lastUsedAt?: string;
  isExpired: boolean;
};

export type CreateApiKeyRequest = {
  name: string;
  expiresAt?: string;
};

export type CreateApiKeyResponse = {
  key: string;
  keyId: string;
  name: string;
};

export class ApiKeyResource extends AbstractResource {
  public async getApiKeys(): Promise<ApiKeyResponse[]> {
    return this.get('/user/api-keys');
  }

  public async createApiKey(
    data: CreateApiKeyRequest,
  ): Promise<CreateApiKeyResponse> {
    return this.post('/user/api-keys', { json: data });
  }

  public async revokeApiKey(keyId: string): Promise<EmptyResponse> {
    return this.delete(`/user/api-keys/${keyId}`);
  }

  public async getShareXConfig(
    baseUrl?: string,
    apiKey?: string,
  ): Promise<any> {
    const params = new URLSearchParams();
    if (baseUrl) params.append('baseUrl', baseUrl);
    if (apiKey) params.append('apiKey', apiKey);

    return this.get(`/user/sharex-config?${params.toString()}`);
  }
}
