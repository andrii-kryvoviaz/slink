import { AbstractResource } from '@slink/api/AbstractResource';
import type { EmptyResponse } from '@slink/api/Response';

export type OAuthProviderDetails = {
  id: string;
  name: string;
  slug: string;
  type: string;
  clientId: string;
  discoveryUrl: string;
  scopes: string;
  enabled: boolean;
};

export type OAuthProviderFormData = {
  name: string;
  slug: string;
  type: string;
  clientId: string;
  clientSecret?: string;
  discoveryUrl: string;
  scopes: string;
  enabled: boolean;
};

export class OAuthResource extends AbstractResource {
  public async list(): Promise<OAuthProviderDetails[]> {
    const response = await this.get<{ data: OAuthProviderDetails[] }>(
      '/admin/oauth/providers',
    );
    return response.data ?? [];
  }

  public async create(
    data: OAuthProviderFormData,
  ): Promise<OAuthProviderDetails> {
    return this.post('/admin/oauth/providers', { json: data });
  }

  public async update(
    id: string,
    data: Partial<OAuthProviderFormData>,
  ): Promise<OAuthProviderDetails> {
    return this.put(`/admin/oauth/providers/${id}`, { json: data });
  }

  public async remove(id: string): Promise<EmptyResponse> {
    return this.delete(`/admin/oauth/providers/${id}`);
  }
}
