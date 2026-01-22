import { createRoute } from '@slink/utils/url/routing';

export const collectionRoutes = {
  list: '/collections',
  detail: createRoute((id: string) => `/collection/${id}`),
};
