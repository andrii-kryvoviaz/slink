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
    const searchParams = new URLSearchParams({
      limit: limit.toString(),
      orderBy,
    });

    if (searchTerm && searchBy) {
      searchParams.append('searchTerm', searchTerm);
      searchParams.append('searchBy', searchBy);
    }

    if (cursor) {
      searchParams.append('cursor', cursor);
    }

    return this.get(`/images?${searchParams.toString()}`);
  }

  public async getHistory(
    limit: number = 10,
    cursor?: string,
    includeTags: boolean = false,
    tagIds?: string[],
    requireAllTags: boolean = false,
  ): Promise<ImageListingResponse> {
    const searchParams = new URLSearchParams({
      limit: limit.toString(),
    });

    if (cursor) {
      searchParams.append('cursor', cursor);
    }

    if (includeTags) {
      searchParams.append('includeTags', 'true');
    }

    if (tagIds && tagIds.length > 0) {
      tagIds.forEach((tagId) => {
        searchParams.append('tagIds[]', tagId);
      });
    }

    if (requireAllTags) {
      searchParams.append('requireAllTags', 'true');
    }

    return this.get(`/images/history?${searchParams.toString()}`);
  }

  public async getImagesByIds(
    uuids: string[],
    includeTags: boolean = false,
  ): Promise<ImagePlainListingResponse> {
    const searchParams = new URLSearchParams();

    uuids.forEach((uuid) => {
      searchParams.append('uuid[]', uuid);
    });

    if (includeTags) {
      searchParams.append('includeTags', 'true');
    }

    return this.get(`/images/by-id?${searchParams.toString()}`);
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
    const searchParams = new URLSearchParams();

    if (params.width !== undefined) {
      searchParams.append('width', params.width.toString());
    }

    if (params.height !== undefined) {
      searchParams.append('height', params.height.toString());
    }

    if (params.crop !== undefined) {
      searchParams.append('crop', params.crop.toString());
    }

    if (params.format !== undefined) {
      searchParams.append('format', params.format);
    }

    if (params.filter !== undefined) {
      searchParams.append('filter', params.filter);
    }

    return this.get(`/image/${id}/share?${searchParams.toString()}`);
  }

  public async getLicenses(): Promise<{ licenses: License[] }> {
    return this.get('/licenses');
  }
}
