import { AbstractResource } from '@slink/api/AbstractResource';
import type { LoginResponse } from '@slink/api/Response/Auth/LoginResponse';

import type { OAuthProvider } from '@slink/lib/enum/OAuthProvider';

export type SsoProvider = {
  id: string;
  name: string;
  slug: OAuthProvider;
};

export type SsoAuthorizeResponse = {
  authorizationUrl: string;
};

export type SsoApprovalRequired = {
  approval_required: true;
  userId: string;
};

export type SsoTokenResponse = LoginResponse | SsoApprovalRequired;

export class SsoResource extends AbstractResource {
  public async getProviders(): Promise<SsoProvider[]> {
    const response = await this.get<{ data: SsoProvider[] }>(
      '/auth/sso/providers',
    );
    return response.data ?? [];
  }

  public async authorize(data: {
    provider: string;
    redirectUri: string;
  }): Promise<SsoAuthorizeResponse> {
    return this.post('/auth/sso/authorize', { json: data });
  }

  public async token(data: {
    code: string;
    state: string;
  }): Promise<SsoTokenResponse> {
    return this.post('/auth/sso/token', { json: data });
  }
}
