import { AbstractResource } from '@slink/api/AbstractResource';
import type {
  ImageDetailsResponse,
  ImageListingResponse,
  UploadedImageResponse,
} from '@slink/api/Response';

export class ImageResource extends AbstractResource {
  public async upload(image: File): Promise<UploadedImageResponse> {
    const body = new FormData();
    body.append('image', image);

    return this.post('/upload', { body });
  }

  public async remove(
    id: string,
    preserveOnDisk: boolean = false
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
    }
  ): Promise<ImageDetailsResponse> {
    return this.patch(`/image/${id}`, {
      json: details,
    });
  }

  public async getPublicImages(
    page: number = 1,
    limit: number = 10,
    orderBy: string = 'attributes.updatedAt'
  ): Promise<ImageListingResponse> {
    return this.get(`/images/${page}/?limit=${limit}&orderBy=${orderBy}`);
  }

  public async getHistory(
    page: number = 1,
    limit: number = 10
  ): Promise<ImageListingResponse> {
    return this.get(`/images/history/${page}/?limit=${limit}`);
  }
}
