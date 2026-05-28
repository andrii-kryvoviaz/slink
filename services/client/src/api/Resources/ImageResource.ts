import { AbstractResource } from '@slink/api/AbstractResource';
import type {
  ImageDetailsResponse,
  ImageListingItem,
  ImageListingResponse,
  ImagePlainListingResponse,
  UploadedImageResponse,
} from '@slink/api/Response';
import type { ShareResponse } from '@slink/api/Response/Share/ShareResponse';

import type { License } from '@slink/lib/enum/License';

export class ImageResource extends AbstractResource {
  public async upload(
    image: File,
    options: {
      tagIds?: string[];
      collectionIds?: string[];
      asGuest?: boolean;
    } = {},
  ): Promise<UploadedImageResponse> {
    const { tagIds = [], collectionIds = [], asGuest = false } = options;
    const body = new FormData();
    body.append('image', image);
    tagIds.forEach((id) => body.append('tagIds[]', id));
    collectionIds.forEach((id) => body.append('collectionIds[]', id));
    return this.post(asGuest ? '/guest/upload' : '/upload', { body });
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
