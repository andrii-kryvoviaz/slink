import { ApiClient } from '@slink/api/Client';
import type { ImageDetailsResponse } from '@slink/api/Response';

import type { PageLoad } from './$types';

export const load: PageLoad = async ({ params, fetch, parent }) => {
  await parent();

  const response: ImageDetailsResponse = await ApiClient.image.getDetails(
    params.id
  );
  return response;
};
