import { AbstractResource } from '@slink/api/AbstractResource';
import type { EmptyResponse } from '@slink/api/Response';
import type { AuthenticatedUser } from '@slink/api/Response/User/AuthenticatedUser';
import type { CheckStatusResponse } from '@slink/api/Response/User/CheckStatusResponse';

export class UserResource extends AbstractResource {
  public async checkStatus(userId: string): Promise<CheckStatusResponse> {
    return this.get(`/public/user/${userId}/status`);
  }

  public async getCurrentUser(
    accessToken?: string
  ): Promise<AuthenticatedUser> {
    const headers = accessToken
      ? { Authorization: `Bearer ${accessToken}` }
      : ({} as Record<string, string>);

    return this.get('/user', { headers });
  }

  public async changePassword({
    old_password,
    password,
    confirm,
  }: {
    old_password: string;
    password: string;
    confirm: string;
  }): Promise<EmptyResponse> {
    return this.post('/user/change-password', {
      json: { old_password, password, confirm },
    });
  }

  public async updateProfile({ display_name }: { display_name: string }) {
    return this.patch('/user/profile', {
      json: { display_name },
    });
  }
}
