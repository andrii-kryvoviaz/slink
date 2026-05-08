import type { RequestHandler } from './$types';

export const GET: RequestHandler = async ({ params, url, fetch }) => {
  const upstream = await fetch(`/api/image/${params.path}${url.search}`, {
    redirect: 'manual',
  });

  return new Response(upstream.body, {
    status: upstream.status,
    statusText: upstream.statusText,
    headers: new Headers(upstream.headers),
  });
};
