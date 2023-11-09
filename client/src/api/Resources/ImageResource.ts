import { AbstractResource } from '@slink/api/AbstractResource';

import type { UploadedImageResponse } from '@slink/api/Response/UploadedImageResponse';
import type { ImageDetailsResponse } from '@slink/api/Response/ImageDetailsResponse';

export class ImageResource extends AbstractResource {
  public async upload(image: File): Promise<UploadedImageResponse> {
    const body = new FormData();
    body.append('image', image);

    return this.post('/upload', { body });
  }

  public async getDetails(id: string): Promise<ImageDetailsResponse> {
    return this.get(`/image/${id}/detail`);
  }
}
