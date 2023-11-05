import { error, type Handle } from '@sveltejs/kit';

import dotenv from 'dotenv';

dotenv.config();

const MY_API_BASE_URL = 'http://localhost:8080';
const PROXY_PATH = '/api';

const handleApiProxy: Handle = async ({ event }) => {
  const origin = event.request.headers.get('Origin');

  console.log('api enabled: ', process.env.API_ENABLED);

  // reject requests that don't come from the webapp, to avoid your proxy being abused.
  if (
    (!origin || new URL(origin).origin !== event.url.origin) &&
    process.env.API_ENABLED !== 'true'
  ) {
    throw error(403, 'Forbidden');
  }

  console.log('Proxying request to: ', event.request.url);

  // strip `/api` from the request path
  const strippedPath = event.url.pathname.substring(PROXY_PATH.length);

  // build the new URL path with your API base URL, the stripped path and the query string
  const urlPath = `${MY_API_BASE_URL}${strippedPath}${event.url.search}`;
  const proxiedUrl = new URL(urlPath);

  // Strip off header added by SvelteKit yet forbidden by underlying HTTP request
  // library `undici`.
  // https://github.com/nodejs/undici/issues/1470
  event.request.headers.delete('connection');

  const options: any = {
    // propagate the request method and body
    body: event.request.body,
    method: event.request.method,
    headers: event.request.headers,
    // is not set explicitly by some Node.js versions, may cause problems with upload
    duplex: 'half',
  };

  return fetch(proxiedUrl.toString(), options).catch((err) => {
    console.log('Could not proxy API request: ', err);
    throw err;
  });
};

export const handle: Handle = async ({ event, resolve }) => {
  // intercept requests to `/api` and handle them with `handleApiProxy`
  if (event.url.pathname.startsWith(PROXY_PATH)) {
    return handleApiProxy({ event, resolve });
  }

  return resolve(event);
};
