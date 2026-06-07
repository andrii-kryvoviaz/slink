import { createUniquePng } from '../../uniqueImage';
import type { HttpClient } from '../HttpClient';

export interface ImageDetail {
  id: string;
  fileName: string;
  mimeType: string;
}

export class ContentApi {
  constructor(private http: HttpClient) {}

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
