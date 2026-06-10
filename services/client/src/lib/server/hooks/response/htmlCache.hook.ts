import type { Handle } from '@sveltejs/kit';

import { defineHook } from '../define';

const preventReroutedHtmlCaching: Handle = async ({ event, resolve }) => {
  const response = await resolve(event);

  if (!/^\/(image|collection)\//.test(event.url.pathname)) {
    return response;
  }

  const contentType = response.headers.get('content-type') ?? '';
  if (!contentType.includes('text/html')) {
    return response;
  }

  response.headers.set('Cache-Control', 'private, no-store');

  return response;
};

export default defineHook({ handle: preventReroutedHtmlCaching });
