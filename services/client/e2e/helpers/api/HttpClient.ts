const API_URL = process.env.E2E_API_URL ?? 'http://localhost:8180';

export class HttpClient {
  private _accessToken: string;

  private constructor(token: string) {
    this._accessToken = token;
  }

  static async createForUser(
    username: string,
    password: string,
  ): Promise<HttpClient> {
    const client = new HttpClient('');
    await client.login(username, password);
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
        throw new Error(`Login failed: ${res.status}`);
      }

      await new Promise((r) => setTimeout(r, 1000 * attempt));
    }
  }

  async request(method: string, path: string, body?: object) {
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

  async requestRaw(
    method: string,
    path: string,
    body?: object,
  ): Promise<{ status: number; data: unknown }> {
    const res = await fetch(`${API_URL}${path}`, {
      method,
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${this._accessToken}`,
      },
      body: body ? JSON.stringify(body) : undefined,
    });

    const text = await res.text();
    let data: unknown = null;
    try {
      data = JSON.parse(text);
    } catch {}

    return { status: res.status, data };
  }

  async postForm(path: string, form: FormData) {
    const res = await fetch(`${API_URL}${path}`, {
      method: 'POST',
      headers: {
        Authorization: `Bearer ${this._accessToken}`,
      },
      body: form,
    });

    const text = await res.text();
    if (!res.ok) {
      throw new Error(`API POST ${path} failed: ${res.status} ${text}`);
    }

    return JSON.parse(text);
  }
}
