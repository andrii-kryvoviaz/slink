import { buildQueryString, createRoute } from '@slink/utils/url/routing';

export type ImageParams = {
  width?: number;
  height?: number;
  crop?: boolean;
  quality?: number;
  format?: string;
};

const withFormat = (fileName: string, format?: string): string => {
  if (!format) {
    return fileName;
  }

  const dotIndex = fileName.lastIndexOf('.');
  const base = dotIndex === -1 ? fileName : fileName.slice(0, dotIndex);

  return `${base}.${format}`;
};

export const imageRoutes = {
  view: createRoute(
    (fileName: string, params?: ImageParams) =>
      `/image/${fileName}${buildQueryString(params ?? {})}`,
  ),
  info: createRoute((imageId: string) => `/info/${imageId}`),
  short: createRoute((shortCode: string) => `/i/${shortCode}`),
};

export const imagePreview = (fileName: string, params: ImageParams): string => {
  const { format, ...query } = params;

  return imageRoutes.view(withFormat(fileName, format), query);
};
