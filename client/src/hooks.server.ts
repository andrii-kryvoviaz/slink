import { env } from '$env/dynamic/private';
import { Theme, setCookieSettingsOnLocals } from '@slink/lib/settings';
import type { Handle } from '@sveltejs/kit';
import { sequence } from '@sveltejs/kit/hooks';

const handleApiProxy: Handle = async ({ event, resolve }) => {
  const { url, fetch, request } = event;
  const pathRegex = new RegExp(`^(${env.PROXY_PREFIXES.replace(';', '|')})`);

  if (!pathRegex.test(url.pathname)) {
    return resolve(event);
  }

  const strippedPath = url.pathname.replace(
    new RegExp(`^${env.API_PREFIX}`),
    ''
  );

  const proxyUrl = `${env.API_URL}${strippedPath}${url.search}`;

  const options: any = {
    method: request.method,
    headers: request.headers,
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

const handleResponseHeaders: Handle = async ({ event, resolve }) => {
  const headers = ['location', 'content-type'];
  return resolve(event, {
    filterSerializedResponseHeaders: (name) => {
      return name.startsWith('x-') || headers.includes(name);
    },
  });
};

const handleTheme: Handle = async ({ event, resolve }) => {
  const theme = event.cookies.get('theme') || Theme.DARK;

  return resolve(event, {
    transformPageChunk: ({ html }) => html.replace('%app.theme%', theme),
  });
};

export const handle = sequence(
  handleResponseHeaders,
  handleApiProxy,
  setCookieSettingsOnLocals,
  handleTheme
);
