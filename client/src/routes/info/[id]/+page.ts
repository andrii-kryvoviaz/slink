import type { PageLoad } from './$types';
import { ApiClient } from '@slink/api/Client';

export const load: PageLoad = async ({ params, fetch, url }) => {
  const response = await ApiClient.image.using(fetch).getDetails(params.id);
  return {
    ...response,
    url: `${url.origin}${response.url}`
  }
};
