const API_URL = 'http://localhost:8080';

export class AdminApiClient {
  private _accessToken: string;

  private constructor(token: string) {
    this._accessToken = token;
  }

  static async create(): Promise<AdminApiClient> {
    const token = process.env.E2E_ADMIN_TOKEN;
    if (token) return new AdminApiClient(token);

    const client = new AdminApiClient('');
    await client.login('e2e', 'E2eTest123!');
    return client;
  }

  get token() {
    return this._accessToken;
  }

  async login(username: string, password: string, retries = 3) {
    for (let attempt = 1; attempt <= retries; attempt++) {
      const res = await fetch(`${API_URL}/api/auth/login`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ username, password }),
      });

      if (res.ok) {
        const data = await res.json();
        this._accessToken = data.access_token;
        return;
      }

      if (attempt === retries) {
        throw new Error(`Admin login failed: ${res.status}`);
      }

      await new Promise((r) => setTimeout(r, 1000 * attempt));
    }
  }

  private async request(method: string, path: string, body?: object) {
    const res = await fetch(`${API_URL}${path}`, {
      method,
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${this._accessToken}`,
      },
      body: body ? JSON.stringify(body) : undefined,
    });

    const text = await res.text();
    let data = null;
    try {
      data = JSON.parse(text);
    } catch {}

    if (!res.ok) {
      throw new Error(`API ${method} ${path} failed: ${res.status} ${text}`);
    }

    return data;
  }

  async getMe() {
    return this.request('GET', '/api/user');
  }

  async getSettings() {
    return this.request('GET', '/api/settings/global');
  }

  async updateSettings(category: string, settings: object) {
    return this.request('POST', '/api/settings', { category, settings });
  }

  async findUserByEmail(email: string) {
    const data = await this.request(
      'GET',
      `/api/users/1?search=${encodeURIComponent(email)}`,
    );
    return data?.data?.[0] ?? null;
  }

  async changeUserStatus(id: string, status: string) {
    return this.request('PATCH', '/api/user/status', { id, status });
  }
}
