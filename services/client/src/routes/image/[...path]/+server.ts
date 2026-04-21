import type { RequestHandler } from './$types';

export const GET: RequestHandler = ({ params, url, fetch }) => {
  return fetch(`/api/image/${params.path}${url.search}`, {
    redirect: 'manual',
  });
};
