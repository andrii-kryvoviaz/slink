import type { Handle } from '@sveltejs/kit';

import { defineHook } from '../define';

const filterResponseHeaders: Handle = async ({ event, resolve }) => {
  const headers = ['location', 'content-type'];
  return resolve(event, {
    filterSerializedResponseHeaders: (name) => {
      return name.startsWith('x-') || headers.includes(name);
    },
  });
};

export default defineHook({ handle: filterResponseHeaders });
