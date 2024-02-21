import type { Handle } from '@sveltejs/kit';
import { cookie, setServerCookiesHandle } from '@slink/utils/cookie';
import { Theme } from '@slink/store/settings';
import { setFetchHandle } from '@slink/api/Client';
import { sequence } from '@sveltejs/kit/hooks';

const handleApiProxy: Handle = async ({ event, resolve }) => {
  const API_BASE_URL = 'http://localhost:8080';
  const PROXY_PATH = '/api';
  const PROXY_PATHS = [PROXY_PATH, '/image'];
  const pathRegex = new RegExp(`^(${PROXY_PATHS.join('|')})`);

  const { url, fetch, request } = event;

  if (!pathRegex.test(url.pathname)) {
    return resolve(event);
  }

  const strippedPath = url.pathname.replace(new RegExp(`^${PROXY_PATH}`), '');
  const proxyUrl = `${API_BASE_URL}${strippedPath}${url.search}`;

  const options: any = {
    body: request.body,
    method: request.method,
    headers: request.headers,
    // may cause problems with uploads in some Node versions if not set
    duplex: 'half',
    // Node doesn't have to verify SSL certificates within the container
    rejectUnauthorized: false,
  };

  return fetch(proxyUrl, options).catch((err) => {
    console.log('Could not proxy API request: ', err);
    throw err;
  });
};

const handleTheme: Handle = async ({ event, resolve }) => {
  return resolve(event, {
    transformPageChunk: ({ html }) =>
      html.replace('%app.theme%', cookie.get('theme', Theme.DARK)),
  });
};

export const handle = sequence(
  setFetchHandle,
  setServerCookiesHandle,
  handleApiProxy,
  handleTheme
);
