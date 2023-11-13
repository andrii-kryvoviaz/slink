import type { Handle } from '@sveltejs/kit';

const API_BASE_URL = 'http://localhost:8080';
const PROXY_PATH = '/api';

const handleApiProxy: Handle = async ({ event }) => {
  // Ensure that PROXY_PATH is escaped properly for use in a regex pattern:
  const escapedProxyPath = PROXY_PATH.replace(/[-[/\]{}()*+?.\\^$|]/g, '\\$&');

  // Create a RegExp object with the escaped proxy path:
  const proxyPathRegex = new RegExp(`^${escapedProxyPath}`);

  // strip `/api` from the request path before proxying
  const strippedPath = event.url.pathname.replace(proxyPathRegex, '');

  // build the new URL path with your API base URL, the stripped path and the query string
  const urlPath = `${API_BASE_URL}${strippedPath}${event.url.search}`;
  const proxiedUrl = new URL(urlPath);

  // Strip off header added by SvelteKit yet forbidden by underlying HTTP request
  event.request.headers.delete('connection');

  const options: any = {
    // propagate the request method and body
    body: event.request.body,
    method: event.request.method,
    headers: event.request.headers,
    // is not set explicitly by some Node.js versions, may cause problems with upload
    duplex: 'half',
    // Node doesn't have to verify SSL certificates within the container
    rejectUnauthorized: false,
  };

  return fetch(proxiedUrl.toString(), options).catch((err) => {
    console.log('Could not proxy API request: ', err);
    throw err;
  });
};

export const handle: Handle = async ({ event, resolve }) => {
  // intercept requests
  const paths = [PROXY_PATH, '/image'];
  const pathRegex = new RegExp(`^(${paths.join('|')})`);

  if (pathRegex.test(event.url.pathname)) {
    return handleApiProxy({ event, resolve });
  }

  return resolve(event);
};
