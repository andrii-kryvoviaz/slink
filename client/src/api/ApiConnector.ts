import { Auth } from '@slink/lib/auth/Auth';
import { Session } from '@slink/lib/auth/Session';
import { type Handle } from '@sveltejs/kit';

type ApiOptions = {
  baseUrl: string;
  urlPrefix: string;
  registeredPaths: string[];
};

export const ApiConnector = (options: ApiOptions): Handle => {
  return async ({ event, resolve }) => {
    const { url, fetch, request, cookies, locals } = event;
    const session = await Session.get(cookies.get('sessionId'));

    const pathRegex = new RegExp(`^(${options.registeredPaths.join('|')})`);

    if (!pathRegex.test(url.pathname)) {
      if (session?.user) {
        locals.user = session.user;
      }

      return resolve(event);
    }

    if (session?.accessToken && !request.headers.has('Authorization')) {
      request.headers.set('Authorization', `Bearer ${session.accessToken}`);
    }

    const strippedPath = url.pathname.replace(
      new RegExp(`^${options.urlPrefix}`),
      ''
    );

    const proxyUrl = `${options.baseUrl}${strippedPath}${url.search}`;

    const requestOptions: any = {
      method: request.method,
      headers: request.headers,
      credentials: 'omit',
    };

    requestOptions.headers.delete('cookie');

    const contentType = requestOptions.headers.get('content-type');

    if (contentType && /^(application\/json.*)/.test(contentType)) {
      requestOptions.body = await request.arrayBuffer();
    }

    if (contentType && /^(multipart\/form-data.*)/.test(contentType)) {
      requestOptions.body = request.clone().body;
      // is required by newer Node.js versions
      requestOptions.duplex = 'half';
    }

    const response = await fetch(proxyUrl, requestOptions);

    if (response.status === 401) {
      const tokens = await Auth.refresh({ cookies, fetch });

      if (!tokens) {
        return response;
      }

      const { accessToken, refreshToken } = tokens;

      requestOptions.headers.set('Authorization', `Bearer ${accessToken}`);

      if (requestOptions.body) {
        requestOptions.body = request.clone().body;
      }

      return await fetch(proxyUrl, requestOptions);
    }

    return response;
  };
};
