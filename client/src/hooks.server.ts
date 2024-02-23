import type { Handle } from '@sveltejs/kit';
import { sequence } from '@sveltejs/kit/hooks';

import { setFetchHandle } from '@slink/api/Client';
import { Theme } from '@slink/store/settings';

import { cookie, setServerCookiesHandle } from '@slink/utils/cookie';

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
    method: request.method,
    headers: request.headers,
    // Node doesn't have to verify SSL certificates within the container
    rejectUnauthorized: false,
  };

  const contentType = request.headers.get('content-type');

  if (contentType && /^(application\/json.*)/.test(contentType)) {
    options.body = await request.arrayBuffer();
  }

  if (contentType && /^(multipart\/form-data.*)/.test(contentType)) {
    options.body = request.body;
    // is required by newer Node.js versions
    options.duplex = 'half';
  }

  return fetch(proxyUrl, options);
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
