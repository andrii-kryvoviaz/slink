import { AbstractResource } from '@slink/api/AbstractResource';
import type {
  ImageDetailsResponse,
  ImageListingResponse,
  ImagePlainListingResponse,
  UploadedImageResponse,
} from '@slink/api/Response';

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
    },
  ): Promise<ImageDetailsResponse> {
    return this.patch(`/image/${id}`, {
      json: details,
    });
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
  ): Promise<ImageListingResponse> {
    const searchParams = new URLSearchParams({
      limit: limit.toString(),
    });

    if (cursor) {
      searchParams.append('cursor', cursor);
    }

    return this.get(`/images/history/${page}/?${searchParams.toString()}`);
  }

  public async getImagesByIds(
    uuids: string[],
  ): Promise<ImagePlainListingResponse> {
    const query = uuids.map((uuid) => `uuid[]=${uuid}`).join('&');

    return this.get(`/images?${query}`);
  }

  public async adminUpdateDetails(
    id: string,
    details: {
      description?: string;
      isPublic?: boolean;
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
}
