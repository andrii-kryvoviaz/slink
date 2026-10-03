import { MERCURE_HUB_URL } from '$app/env/private';

import type { RequestHandler } from './$types';

export const GET: RequestHandler = async ({ url, cookies }) => {
  const mercureUrl =
    MERCURE_HUB_URL || 'http://localhost:3333/.well-known/mercure';

  const topic = url.searchParams.get('topic');

  if (!topic) {
    return new Response('Missing topic parameter', { status: 400 });
  }

  const jwt = cookies.get('mercureAuthorization');

  const targetUrl = new URL(mercureUrl);
  targetUrl.searchParams.append('topic', topic);

  const response = await fetch(targetUrl.toString(), {
    headers: {
      Accept: 'text/event-stream',
      ...(jwt ? { Authorization: `Bearer ${jwt}` } : {}),
    },
  });

  if (!response.ok) {
    return new Response('Failed to connect to Mercure hub', {
      status: response.status,
    });
  }

  return new Response(response.body, {
    headers: {
      'Content-Type': 'text/event-stream',
      'Cache-Control': 'no-cache',
      Connection: 'keep-alive',
    },
  });
};
