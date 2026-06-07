import { AbstractResource } from '@slink/api/AbstractResource';
import type {
  ImageDetailsResponse,
  ImageListingItem,
  ImageListingResponse,
  ImagePlainListingResponse,
  UploadChunkResponse,
  UploadCompleteResponse,
  UploadInitResponse,
  UploadStatusResponse,
} from '@slink/api/Response';
import type { ShareResponse } from '@slink/api/Response/Share/ShareResponse';

import type { License } from '@slink/lib/enum/License';

export class ImageResource extends AbstractResource {
  public async initUpload(
    params: {
      fileName: string;
      totalSize: number;
      mimeType: string;
      isPublic?: boolean;
      description?: string;
      tagIds?: string[];
      collectionIds?: string[];
    },
    signal?: AbortSignal,
  ): Promise<UploadInitResponse> {
    return this.post('/upload/chunked', {
      json: {
        isPublic: false,
        description: '',
        tagIds: [],
        collectionIds: [],
        ...params,
      },
      signal,
    });
  }

  public async putChunk(
    uploadId: string,
    index: number,
    chunk: Blob,
    token: string,
    options: { complete?: boolean } = {},
    signal?: AbortSignal,
  ): Promise<UploadChunkResponse | UploadCompleteResponse> {
    const headers: Record<string, string> = {
      'X-Upload-Token': token,
      'Content-Type': 'application/octet-stream',
    };

    if (options.complete) {
      headers['X-Upload-Complete'] = 'true';
    }

    return this.put(`/upload/chunked/${uploadId}/${index}`, {
      body: chunk,
      headers,
      signal,
    });
  }

  public async getUploadStatus(
    uploadId: string,
    token: string,
    signal?: AbortSignal,
  ): Promise<UploadStatusResponse> {
    return this.get(`/upload/chunked/${uploadId}`, {
      headers: { 'X-Upload-Token': token },
      signal,
    });
  }

  public async abortUpload(uploadId: string, token: string): Promise<void> {
    return this.delete(`/upload/chunked/${uploadId}`, {
      headers: { 'X-Upload-Token': token },
    });
  }

  public async remove(
    id: string,
    preserveOnDisk: boolean = false,
  ): Promise<void> {
    return this.delete(`/image/${id}`, {
      json: { preserveOnDisk },
    });
  }

  public async batchRemove(
    imageIds: string[],
    preserveOnDisk: boolean = false,
  ): Promise<{
    deleted: string[];
    failed: Array<{ id: string; reason: string }>;
  }> {
    return this.delete('/images/batch-delete', {
      json: { imageIds, preserveOnDisk },
    });
  }

  public async batchReassign(
    assignments: Record<
      string,
      { tagIds?: string[]; collectionIds?: string[] }
    >,
  ): Promise<{
    processed: string[];
    failed: Array<{ id: string; reason: string }>;
  }> {
    return this.put('/images/batch', {
      json: { assignments },
    });
  }

  public async batch(
    imageIds: string[],
    data: {
      isPublic?: boolean;
    },
  ): Promise<{
    processed: string[];
    failed: Array<{ id: string; reason: string }>;
  }> {
    return this.patch('/images/batch', {
      json: { imageIds, ...data },
    });
  }

  public async getDetails(id: string): Promise<ImageDetailsResponse> {
    return this.get(`/image/${id}/detail`);
  }

  public async updateDetails(
    id: string,
    details: {
      description?: string;
      isPublic?: boolean;
      license?: string;
    },
  ): Promise<ImageDetailsResponse> {
    return this.patch(`/image/${id}`, {
      json: details,
    });
  }

  public async getPublicImageById(id: string): Promise<ImageListingItem> {
    return this.get(`/image/${id}/public`);
  }

  public async getPublicImages(
    limit: number = 10,
    orderBy: string = 'attributes.updatedAt',
    searchTerm?: string,
    searchBy?: string,
    cursor?: string,
  ): Promise<ImageListingResponse> {
    return this.get('/images', {
      query: { limit, orderBy, searchTerm, searchBy, cursor },
    });
  }

  public async existsPublicImages(
    filters: {
      searchTerm?: string;
      searchBy?: string;
      tagIds?: string[];
      requireAllTags?: boolean;
    } = {},
  ): Promise<boolean> {
    const response = await this.get<{ exists: boolean }>('/images/exists', {
      query: filters as Record<string, unknown>,
    });
    return response.exists;
  }

  public async getHistory(
    limit: number = 10,
    cursor?: string,
    includeTags: boolean = false,
    tagIds?: string[],
    requireAllTags: boolean = false,
  ): Promise<ImageListingResponse> {
    return this.get('/images/history', {
      query: {
        limit,
        cursor,
        includeTags: includeTags || undefined,
        tagIds,
        requireAllTags: requireAllTags || undefined,
      },
    });
  }

  public async existsHistory(
    filters: {
      searchTerm?: string;
      searchBy?: string;
      tagIds?: string[];
      requireAllTags?: boolean;
    } = {},
  ): Promise<boolean> {
    const response = await this.get<{ exists: boolean }>(
      '/images/history/exists',
      {
        query: filters as Record<string, unknown>,
      },
    );
    return response.exists;
  }

  public async getImagesByIds(
    uuids: string[],
    includeTags: boolean = false,
  ): Promise<ImagePlainListingResponse> {
    return this.get('/images/by-id', {
      query: {
        uuid: uuids,
        includeTags: includeTags || undefined,
      },
    });
  }

  public async adminUpdateDetails(
    id: string,
    details: {
      description?: string;
      isPublic?: boolean;
      license?: string;
    },
  ): Promise<void> {
    return this.patch(`/admin/image/${id}`, {
      json: details,
    });
  }

  public async adminRemove(
    id: string,
    preserveOnDisk: boolean = false,
  ): Promise<void> {
    return this.delete(`/admin/image/${id}`, {
      json: { preserveOnDisk },
    });
  }

  public async shareImage(
    id: string,
    params: {
      width?: number;
      height?: number;
      crop?: boolean;
      format?: string;
      filter?: string;
    },
  ): Promise<ShareResponse> {
    return this.get(`/image/${id}/share`, {
      query: params as Record<string, unknown>,
    });
  }

  public async publishShare(shareId: string): Promise<void> {
    return this.put(`/share/${shareId}/publish`);
  }

  public async getLicenses(): Promise<{ licenses: License[] }> {
    return this.get('/licenses');
  }
}
