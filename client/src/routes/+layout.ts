import type { LayoutLoad } from './$types';
import { ApiClient } from '@slink/api/Client';

export const load: LayoutLoad = async ({ params, fetch, url }) => {
  ApiClient.use(fetch);
  // ToDo: Investigate race condition of layout load function which might be executed before page load function. This might eliminate the need for passing fetch function on every page.
};
