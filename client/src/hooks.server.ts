import type { Handle } from '@sveltejs/kit';
import { cookie } from '@slink/utils/cookie';
import { Theme } from '@slink/store/settings';

const API_BASE_URL = 'http://localhost:8080';
const PROXY_PATH = '/api';

const handleApiProxy: Handle = async ({ event }) => {
  const escapedProxyPath = PROXY_PATH.replace(/[-[/\]{}()*+?.\\^$|]/g, '\\$&');

  const proxyPathRegex = new RegExp(`^${escapedProxyPath}`);

  // strip `/api` from the request path before proxying
  const strippedPath = event.url.pathname.replace(proxyPathRegex, '');

  const urlPath = `${API_BASE_URL}${strippedPath}${event.url.search}`;
  const proxiedUrl = new URL(urlPath);

  event.request.headers.delete('connection');

  const options: any = {
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
  const paths = [PROXY_PATH, '/image'];
  const pathRegex = new RegExp(`^(${paths.join('|')})`);

  cookie.setServerCookies(event.cookies || []);

  if (pathRegex.test(event.url.pathname)) {
    return handleApiProxy({ event, resolve });
  }

  return resolve(event, {
    transformPageChunk: ({ html }) =>
      html.replace('%app.theme%', cookie.get('theme', Theme.DARK)),
  });
};
