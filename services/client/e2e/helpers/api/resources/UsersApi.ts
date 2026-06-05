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

  async changeUserStatus(id: string, status: string) {
    return this.http.request('PATCH', '/api/user/status', { id, status });
  }
}
