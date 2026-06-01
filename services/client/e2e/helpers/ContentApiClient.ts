import { createUniquePng } from './uniqueImage';

const API_URL = process.env.E2E_API_URL ?? 'http://localhost:8180';

export interface ShareInfo {
  shareId: string;
  shareUrl: string;
  type: 'image' | 'collection';
  created: boolean;
  expiresAt: string | null;
  requiresPassword: boolean;
}

export class ContentApiClient {
  private _accessToken: string;

  private constructor(token: string) {
    this._accessToken = token;
  }

  static async create(): Promise<ContentApiClient> {
    const token = process.env.E2E_ADMIN_TOKEN;
    if (token) return new ContentApiClient(token);

    const client = new ContentApiClient('');
    await client.login('e2e', 'E2eTest123!');
    return client;
  }

  static async createForUser(
    username: string,
    password: string,
  ): Promise<ContentApiClient> {
    const client = new ContentApiClient('');
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
        throw new Error(`Content login failed: ${res.status}`);
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

  async uploadImage(
    options: { isPublic?: boolean; file?: Blob; fileName?: string } = {},
  ): Promise<string> {
    const unique = createUniquePng();
    const { isPublic = false, fileName = unique.name } = options;

    const blob =
      options.file ??
      new Blob([unique.buffer], {
        type: 'image/png',
      });

    const form = new FormData();
    form.append('image', blob, fileName);
    form.append('isPublic', isPublic ? '1' : '0');

    const res = await fetch(`${API_URL}/api/upload`, {
      method: 'POST',
      headers: {
        Authorization: `Bearer ${this._accessToken}`,
      },
      body: form,
    });

    const text = await res.text();
    if (!res.ok) {
      throw new Error(`API POST /api/upload failed: ${res.status} ${text}`);
    }

    const data = JSON.parse(text);
    return data.id ?? data.data?.id ?? data;
  }

  async createCollection(options: {
    name: string;
    description?: string;
  }): Promise<string> {
    const data = await this.request('POST', '/api/collection', {
      name: options.name,
      description: options.description ?? '',
    });

    return data?.id ?? data?.data?.id ?? data;
  }

  async addImageToCollection(
    collectionId: string,
    imageId: string,
  ): Promise<void> {
    await this.request(
      'POST',
      `/api/collection/${collectionId}/items/${imageId}`,
    );
  }

  async createImageShare(imageId: string): Promise<ShareInfo> {
    const data = await this.request('GET', `/api/image/${imageId}/share`);
    return this.toShareInfo(data);
  }

  async createCollectionShare(collectionId: string): Promise<ShareInfo> {
    const data = await this.request(
      'POST',
      `/api/collection/${collectionId}/share`,
    );
    return this.toShareInfo(data);
  }

  async publishShare(shareId: string): Promise<void> {
    await this.request('PUT', `/api/share/${shareId}/publish`);
  }

  async unpublishShare(shareId: string): Promise<void> {
    await this.request('PUT', `/api/share/${shareId}/unpublish`);
  }

  async setSharePassword(shareId: string, password: string): Promise<void> {
    await this.request('PUT', `/api/share/${shareId}/password`, { password });
  }

  async setShareExpiration(shareId: string, expiresAt: string): Promise<void> {
    await this.request('PUT', `/api/share/${shareId}/expiration`, {
      expiresAt,
    });
  }

  async publishImageShare(imageId: string): Promise<ShareInfo> {
    const share = await this.createImageShare(imageId);
    await this.publishShare(share.shareId);
    return this.createImageShare(imageId);
  }

  async publishCollectionShare(collectionId: string): Promise<ShareInfo> {
    const share = await this.createCollectionShare(collectionId);
    await this.publishShare(share.shareId);
    return this.createCollectionShare(collectionId);
  }

  getShortCode(share: ShareInfo): string | null {
    const match = share.shareUrl.match(/\/(?:i|c)\/([^/?#]+)/);
    return match ? match[1] : null;
  }

  getPublicShareUrl(share: ShareInfo): string {
    return share.shareUrl;
  }

  private toShareInfo(data: Record<string, unknown>): ShareInfo {
    return {
      shareId: String(data.shareId),
      shareUrl: String(data.shareUrl),
      type: data.type as 'image' | 'collection',
      created: Boolean(data.created),
      expiresAt: (data.expiresAt as string | null) ?? null,
      requiresPassword: Boolean(data.requiresPassword),
    };
  }
}
