import type { RequestHandler } from './$types';

export const GET: RequestHandler = async ({ params, url, fetch }) => {
  const upstream = await fetch(`/api/image/${params.path}${url.search}`, {
    redirect: 'manual',
  });

  const headers = new Headers(upstream.headers);

  if (upstream.ok) {
    headers.set('Access-Control-Allow-Origin', '*');
    headers.set('Cross-Origin-Resource-Policy', 'cross-origin');
  }

  return new Response(upstream.body, {
    status: upstream.status,
    statusText: upstream.statusText,
    headers,
  });
};
