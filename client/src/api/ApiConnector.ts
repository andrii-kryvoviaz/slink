import { Auth } from '@slink/lib/auth/Auth';
import { Session } from '@slink/lib/auth/Session';
import { type Handle } from '@sveltejs/kit';

import { cloneRequestBody } from '@slink/utils/http/cloneRequestBody';
import { getResponseWithCookies } from '@slink/utils/http/cookie';

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

    if (
      session?.accessToken &&
      !request.headers.has('Authorization') &&
      !/.+\/.+\..{3,4}$/.test(url.pathname)
    ) {
      request.headers.set('Authorization', `Bearer ${session.accessToken}`);
    }

    const strippedPath = url.pathname.replace(
      new RegExp(`^${options.urlPrefix}`),
      ''
    );

    const proxyUrl = `${options.baseUrl}${strippedPath}${url.search}`;

    const { method, headers } = request;
    const body = await cloneRequestBody(request);

    const requestOptions: any = {
      method,
      headers,
      body,
      credentials: 'omit',
      duplex: 'half',
    };

    requestOptions.headers.delete('cookie');

    let response = await fetch(proxyUrl, requestOptions);

    if (response.status === 401) {
      const tokens = await Auth.refresh({ cookies, fetch });

      if (tokens) {
        const { accessToken } = tokens;

        if (request.headers.has('Authorization')) {
          requestOptions.headers.set('Authorization', `Bearer ${accessToken}`);
        }

        if (requestOptions.body) {
          requestOptions.body = await cloneRequestBody(request);
        }

        response = await fetch(proxyUrl, requestOptions);
      }
    }

    return getResponseWithCookies(response, cookies);
  };
};
