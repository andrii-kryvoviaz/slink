import { createRoute } from '@slink/utils/url/routing';

export const imageRoutes = {
  view: createRoute((fileName: string) => `/image/${fileName}`),
  info: createRoute((imageId: string) => `/info/${imageId}`),
  short: createRoute((shortCode: string) => `/i/${shortCode}`),
};
