import type { PageLoad } from './$types';
import { ApiClient } from '@slink/api/Client';

export const load: PageLoad = async ({ params, fetch }) => {
  return await ApiClient.image.using(fetch).getDetails(params.id);
};
