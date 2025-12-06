import { imageRoutes } from './image';
import { shareRoutes } from './share';

export type { ImageParams } from './image';
export { imageRoutes } from './image';
export { shareRoutes } from './share';

export const routes = {
  image: imageRoutes,
  share: shareRoutes,
};
