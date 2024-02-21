import type { PageLoad } from './$types';
import { ApiClient } from '@slink/api/Client';

export const load: PageLoad = async ({ params, fetch, url }) => {
  ApiClient.use(fetch);
  const response = await ApiClient.image.getDetails(params.id);
  return {
    ...response,
    url: `${url?.origin}${response.url}`,
  };
};
