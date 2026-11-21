import { MediaProxy } from '@slink/lib/server/media/MediaProxy';

import type { RequestHandler } from './$types';

export const GET: RequestHandler = (event) => MediaProxy.forward(event);
