import type { Handle } from '@sveltejs/kit';
import { json } from '@sveltejs/kit';

import { defineHook } from '../define';

const handleWellKnownRequests: Handle = async ({ event, resolve }) => {
  const { pathname } = event.url;

  if (pathname.startsWith('/.well-known/')) {
    if (pathname.endsWith('.json')) {
      return json({});
    }

    return new Response('Not Found', { status: 404 });
  }

  return resolve(event);
};

export default defineHook({ handle: handleWellKnownRequests });
