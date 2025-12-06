import type { ShareImageResponse } from '@slink/api/Resources/ImageResource';

import { imageRoutes } from '@slink/utils/url/routes/image';
import { createRoute } from '@slink/utils/url/routing';

export const shareRoutes = {
  fromResponse: createRoute((response: ShareImageResponse) =>
    response.type === 'shortUrl'
      ? imageRoutes.short.path(response.shortCode)
      : response.targetUrl,
  ),
};
