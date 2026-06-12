import { createUniquePng } from '../../uniqueImage';
import type { HttpClient } from '../HttpClient';

export interface ImageDetail {
  id: string;
  fileName: string;
  mimeType: string;
}

export interface CollectionSummary {
  id: string;
}

export interface CollectionItemSummary {
  itemId: string;
}

export class ContentApi {
  constructor(private http: HttpClient) {}

  async listCollections(): Promise<CollectionSummary[]> {
    const data = await this.http.request('GET', '/api/collections');
    const items = (data?.data ?? data ?? []) as Array<{ id: unknown }>;

    return items.map((item) => ({ id: String(item.id) }));
  }

  async getCollection(id: string): Promise<{ status: number; data: unknown }> {
    return this.http.requestRaw('GET', `/api/collection/${id}`);
  }

  async getCollectionSharing(id: string): Promise<{ shareUrl: string } | null> {
    const { data } = await this.getCollection(id);
    const payload = (data as { data?: unknown })?.data ?? data;
    const sharing = (payload as { sharing?: unknown })?.sharing;

    if (!sharing || typeof sharing !== 'object') {
      return null;
    }

    return { shareUrl: String((sharing as { shareUrl?: unknown }).shareUrl) };
  }

  async getCollectionItems(id: string): Promise<CollectionItemSummary[]> {
    const data = await this.http.request('GET', `/api/collection/${id}/items`);
    const items = (data?.data ?? data ?? []) as Array<{ itemId: unknown }>;

    return items.map((item) => ({ itemId: String(item.itemId) }));
  }

  async listHistoryIds(limit = 100): Promise<string[]> {
    const data = await this.http.request(
      'GET',
      `/api/images/history?limit=${limit}`,
    );
    const items = (data?.data ?? []) as Array<{ id: unknown }>;

    return items.map((item) => String(item.id));
  }

  async getImageDetail(imageId: string): Promise<ImageDetail> {
    const data = await this.http.request('GET', `/api/image/${imageId}/detail`);
    const payload = data?.data ?? data;

    return {
      id: String(payload.id),
      fileName: String(payload.fileName),
      mimeType: String(payload.mimeType),
    };
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

    const data = await this.http.postForm('/api/upload', form);
    return data.id ?? data.data?.id ?? data;
  }

  async createCollection(options: {
    name: string;
    description?: string;
  }): Promise<string> {
    const data = await this.http.request('POST', '/api/collection', {
      name: options.name,
      description: options.description ?? '',
    });

    return data?.id ?? data?.data?.id ?? data;
  }

  async addImageToCollection(
    collectionId: string,
    imageId: string,
  ): Promise<void> {
    await this.http.request(
      'POST',
      `/api/collection/${collectionId}/items/${imageId}`,
    );
  }
}
