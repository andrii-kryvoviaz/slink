import { type UserRole, type UserStatus } from '@slink/lib/auth/Type/User';

import { AbstractResource } from '@slink/api/AbstractResource';
import type { UserListFilter } from '@slink/api/Request/UserRequest';
import type { EmptyResponse, UserListingResponse } from '@slink/api/Response';
import type { AuthenticatedUser } from '@slink/api/Response/User/AuthenticatedUser';
import type { CheckStatusResponse } from '@slink/api/Response/User/CheckStatusResponse';
import type { SingleUserResponse } from '@slink/api/Response/User/SingleUserResponse';

export class UserResource extends AbstractResource {
  public async checkStatus(userId: string): Promise<CheckStatusResponse> {
    return this.get(`/user/${userId}/status`);
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

  public async getUsers(
    page: number = 1,
    { limit = 10, orderBy = 'updatedAt', searchTerm = null }: UserListFilter
  ): Promise<UserListingResponse> {
    const urlParams = new URLSearchParams();

    urlParams.append('limit', limit.toString());
    urlParams.append('orderBy', orderBy);

    if (searchTerm) {
      urlParams.append('search', searchTerm);
    }

    return this.get(`/users/${page}/?${urlParams.toString()}`);
  }

  public async changeUserStatus(
    id: string,
    status: UserStatus
  ): Promise<SingleUserResponse> {
    return this.patch(`/user/status`, { json: { id, status } });
  }

  public async grantRole(
    id: string,
    role: UserRole
  ): Promise<SingleUserResponse> {
    return this.post(`/user/role`, { json: { id, role } });
  }

  public async revokeRole(
    id: string,
    role: UserRole
  ): Promise<SingleUserResponse> {
    return this.delete(`/user/role`, { json: { id, role } });
  }
}
