import type { Account } from '../../accounts';
import type { HttpClient } from '../HttpClient';

export class UsersApi {
  constructor(private http: HttpClient) {}

  async getMe() {
    return this.http.request('GET', '/api/user');
  }

  async findUserByEmail(email: string) {
    const data = await this.http.request(
      'GET',
      `/api/users/1?search=${encodeURIComponent(email)}`,
    );
    return data?.data?.[0] ?? null;
  }

  async signUp(account: Account) {
    return this.http.requestRaw('POST', '/api/auth/signup', {
      email: account.email,
      username: account.username,
      password: account.password,
      confirm: account.password,
    });
  }

  async grantRole(id: string, role: string) {
    return this.http.request('POST', '/api/user/role', { id, role });
  }

  async changeUserStatus(id: string, status: string) {
    return this.http.request('PATCH', '/api/user/status', { id, status });
  }

  async purgeUser(id: string) {
    return this.http.request('DELETE', `/api/user/${id}`);
  }
}
