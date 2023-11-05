import { AbstractResource } from '../AbstractResource';

export class ImageResource extends AbstractResource {
  public async upload(files: File[]) {
    const body = files.reduce((formData, file) => {
      formData.append('image', file);
      return formData;
    }, new FormData());

    return this.post('/upload', { body });
  }
}
