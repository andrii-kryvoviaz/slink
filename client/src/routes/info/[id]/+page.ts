import { ApiClient } from '../../../api/Client';

import type { PageLoad } from './$types';

export const load: PageLoad = async ({ params, fetch }) => {
  const response = await ApiClient.image.using(fetch).getDetails(params.id);

  return response;
};
