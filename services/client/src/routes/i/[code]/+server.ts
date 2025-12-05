import { env } from '$env/dynamic/private';

import type { RequestHandler } from './$types';

export const GET: RequestHandler = async ({ params, fetch }) => {
  const apiUrl = env.API_URL || 'http://localhost:8080';
  const response = await fetch(`${apiUrl}/i/${params.code}`, {
    redirect: 'manual',
  });

  if (response.status === 302 || response.status === 301) {
    const location = response.headers.get('Location');
    if (location) {
      const redirectUrl = location.startsWith('/')
        ? `${apiUrl}${location}`
        : location;
      return new Response(null, {
        status: 302,
        headers: {
          Location: redirectUrl,
        },
      });
    }
  }

  return new Response(response.body, {
    status: response.status,
    headers: response.headers,
  });
};
