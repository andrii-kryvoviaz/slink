import { AbstractResource } from '@slink/api/AbstractResource';
import type { AuthenticatedUser } from '@slink/api/Response/User/AuthenticatedUser';
import type { CheckStatusResponse } from '@slink/api/Response/User/CheckStatusResponse';

export class UserResource extends AbstractResource {
  public async checkStatus(userId: string): Promise<CheckStatusResponse> {
    return this.get(`/user/${userId}/status`);
  }

  public async getCurrentUser(): Promise<AuthenticatedUser> {
    return this.get('/user');
  }
}
