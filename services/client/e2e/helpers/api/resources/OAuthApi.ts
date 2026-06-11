import type { HttpClient } from '../HttpClient';

export type OAuthRegistrationPolicy = 'inherit' | 'allowed' | 'blocked';
export type OAuthApprovalPolicy = 'inherit' | 'required' | 'none';

export type OAuthProviderPayload = {
  name: string;
  slug: string;
  type: string;
  clientId: string;
  clientSecret: string;
  discoveryUrl: string;
  scopes: string;
  enabled: boolean;
  registrationPolicy: OAuthRegistrationPolicy;
  approvalPolicy: OAuthApprovalPolicy;
};

export type OAuthProviderRecord = {
  id: string;
  name: string;
  slug: string;
  enabled: boolean;
  registrationPolicy: OAuthRegistrationPolicy;
  approvalPolicy: OAuthApprovalPolicy;
};

export class OAuthApi {
  constructor(private http: HttpClient) {}

  async listProviders(): Promise<OAuthProviderRecord[]> {
    const response = await this.http.request(
      'GET',
      '/api/admin/oauth/providers',
    );
    return response?.data ?? [];
  }

  async createProvider(payload: OAuthProviderPayload): Promise<string> {
    const response = await this.http.request(
      'POST',
      '/api/admin/oauth/providers',
      payload,
    );
    return response.id;
  }

  async updateProvider(id: string, payload: Partial<OAuthProviderPayload>) {
    return this.http.request(
      'PUT',
      `/api/admin/oauth/providers/${id}`,
      payload,
    );
  }

  async deleteProvider(id: string) {
    return this.http.request('DELETE', `/api/admin/oauth/providers/${id}`);
  }
}
