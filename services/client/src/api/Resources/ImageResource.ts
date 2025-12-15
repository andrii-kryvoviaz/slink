import { AbstractResource } from '@slink/api/AbstractResource';
import type {
  ImageDetailsResponse,
  ImageListingItem,
  ImageListingResponse,
  ImagePlainListingResponse,
  UploadedImageResponse,
} from '@slink/api/Response';

import type { License } from '@slink/lib/enum/License';

export class ImageResource extends AbstractResource {
  public async upload(
    image: File,
    tagIds?: string[],
  ): Promise<UploadedImageResponse> {
    const body = new FormData();
    body.append('image', image);

    if (tagIds && tagIds.length > 0) {
      tagIds.forEach((tagId) => body.append('tagIds[]', tagId));
    }

    return this.post('/upload', { body });
  }

  public async guestUpload(
    image: File,
    tagIds?: string[],
  ): Promise<UploadedImageResponse> {
    const body = new FormData();
    body.append('image', image);

    if (tagIds && tagIds.length > 0) {
      tagIds.forEach((tagId) => body.append('tagIds[]', tagId));
    }

    return this.post('/guest/upload', { body });
  }

  public async remove(
    id: string,
    preserveOnDisk: boolean = false,
  ): Promise<void> {
    return this.delete(`/image/${id}`, {
      json: { preserveOnDisk },
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
    page: number = 1,
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

    return this.get(`/images/${page}/?${searchParams.toString()}`);
  }

  public async getHistory(
    page: number = 1,
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

    return this.get(`/images/history/${page}/?${searchParams.toString()}`);
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

    return this.get(`/images?${searchParams.toString()}`);
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
    },
  ): Promise<ShareImageResponse> {
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

    return this.get(`/image/${id}/share?${searchParams.toString()}`);
  }

  public async getLicenses(): Promise<{ licenses: License[] }> {
    return this.get('/licenses');
  }
}

export type ShareImageResponse =
  | { type: 'shortUrl'; shortCode: string }
  | { type: 'signed'; targetUrl: string };
