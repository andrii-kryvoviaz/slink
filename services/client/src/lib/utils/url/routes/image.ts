import { buildQueryString, createRoute } from '@slink/utils/url/routing';

export type ImageParams = {
  width?: number;
  height?: number;
  crop?: boolean;
};

export const imageRoutes = {
  view: createRoute(
    (fileName: string, params?: ImageParams) =>
      `/image/${fileName}${buildQueryString(params ?? {})}`,
  ),
  info: createRoute((imageId: string) => `/info/${imageId}`),
  short: createRoute((shortCode: string) => `/i/${shortCode}`),
};
