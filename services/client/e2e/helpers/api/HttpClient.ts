import fs from 'fs';
import path from 'path';

const API_URL = process.env.E2E_API_URL ?? 'http://localhost:8180';

export const API_TOKEN_PATH = path.join(
  process.cwd(),
  'e2e',
  '.auth',
  'api-token',
);

export class HttpClient {
  private _accessToken: string;

  private constructor(token: string) {
    this._accessToken = token;
  }

  static async create(): Promise<HttpClient> {
    if (fs.existsSync(API_TOKEN_PATH)) {
      return new HttpClient(fs.readFileSync(API_TOKEN_PATH, 'utf8').trim());
    }

    const client = new HttpClient('');
    await client.login('e2e', 'E2eTest123!');
    return client;
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
