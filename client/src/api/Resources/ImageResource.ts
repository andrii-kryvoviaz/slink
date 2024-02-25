import { AbstractResource } from '@slink/api/AbstractResource';
import type {
  ImageDetailsResponse,
  UploadedImageResponse,
} from '@slink/api/Response';

export class ImageResource extends AbstractResource {
  public async upload(image: File): Promise<UploadedImageResponse> {
    const body = new FormData();
    body.append('image', image);

    return this.post('/upload', { body });
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
}
