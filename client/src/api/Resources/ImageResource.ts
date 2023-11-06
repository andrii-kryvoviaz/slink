import { AbstractResource } from '../AbstractResource';

import type { UploadedImageResponse } from '../Response/UploadedImageResponse';

export class ImageResource extends AbstractResource {
  public async upload(image: File): Promise<UploadedImageResponse> {
    const body = new FormData();
    body.append('image', image);

    return this.post('/upload', { body });
  }
}
