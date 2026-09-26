import { authRoutes } from './auth';
import { bookmarkRoutes } from './bookmark';
import { collectionRoutes } from './collection';
import { generalRoutes } from './general';
import { imageRoutes } from './image';
import { shareRoutes } from './share';
import { ssoRoutes } from './sso';

export { authRoutes } from './auth';
export { bookmarkRoutes } from './bookmark';
export { collectionRoutes } from './collection';
export { generalRoutes } from './general';
export { imageRoutes } from './image';
export { shareRoutes } from './share';
export { ssoRoutes } from './sso';

export const routes = {
  auth: authRoutes,
  bookmark: bookmarkRoutes,
  collection: collectionRoutes,
  general: generalRoutes,
  image: imageRoutes,
  share: shareRoutes,
  sso: ssoRoutes,
};
