import type { Handle } from '@sveltejs/kit';

import { defineHook } from '../define';

const handleLinkHeaderPreloading: Handle = async ({ event, resolve }) => {
  const response = await resolve(event);

  if (!response.headers.has('link')) {
    return response;
  }

  const linkHeader = response.headers.get('link');
  if (linkHeader && linkHeader.length >= 4096) {
    response.headers.delete('link');
  }

  return response;
};

export default defineHook({ handle: handleLinkHeaderPreloading });
