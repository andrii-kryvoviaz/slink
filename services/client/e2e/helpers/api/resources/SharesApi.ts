import type { HttpClient } from '../HttpClient';

export interface ShareInfo {
  shareId: string;
  shareUrl: string;
  type: 'image' | 'collection';
  created: boolean;
  expiresAt: string | null;
  requiresPassword: boolean;
}

export class SharesApi {
  constructor(private http: HttpClient) {}

  async createImageShare(imageId: string): Promise<ShareInfo> {
    const data = await this.http.request('GET', `/api/image/${imageId}/share`);
    return this.toShareInfo(data);
  }

  async createCollectionShare(collectionId: string): Promise<ShareInfo> {
    const data = await this.http.request(
      'POST',
      `/api/collection/${collectionId}/share`,
    );
    return this.toShareInfo(data);
  }

  async publishShare(shareId: string): Promise<void> {
    await this.http.request('PUT', `/api/share/${shareId}/publish`);
  }

  async unpublishShare(shareId: string): Promise<void> {
    await this.http.request('PUT', `/api/share/${shareId}/unpublish`);
  }

  async setSharePassword(shareId: string, password: string): Promise<void> {
    await this.http.request('PUT', `/api/share/${shareId}/password`, {
      password,
    });
  }

  async setShareExpiration(shareId: string, expiresAt: string): Promise<void> {
    await this.http.request('PUT', `/api/share/${shareId}/expiration`, {
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
