import { buildQueryString, createRoute } from '@slink/utils/url/routing';

export const generalRoutes = {
  home: '/',
  explore: '/explore',
  history: '/history',
  upload: '/upload',
  uploadToCollection: createRoute(
    (collectionId: string) =>
      `/upload${buildQueryString({ collection: collectionId })}`,
  ),
};
