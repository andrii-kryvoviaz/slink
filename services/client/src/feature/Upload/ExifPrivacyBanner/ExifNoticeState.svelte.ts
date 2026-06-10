import { page } from '$app/state';

export class ExifNoticeState {
  get metadataKept(): boolean {
    return !(page.data.uploadPolicy?.stripExif ?? true);
  }

  get visible(): boolean {
    return this.metadataKept && !page.data.settings.banners.hideExifKeptNotice;
  }

  dismiss(): void {
    const settings = page.data.settings;
    settings.banners = { ...settings.banners, hideExifKeptNotice: true };
  }
}

const exifNotice = new ExifNoticeState();

export const useExifNotice = (): ExifNoticeState => exifNotice;
