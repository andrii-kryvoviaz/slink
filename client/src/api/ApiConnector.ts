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

const refreshRequests: Map<string, Promise<any>> = new Map();

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

    const strippedPath = url.pathname.replace(
      new RegExp(`^${options.urlPrefix}`),
      ''
    );

    const proxyUrl = `${options.baseUrl}${strippedPath}${url.search}`;

    const { method } = request;

    const headers = new Headers({
      'Content-Type': request.headers.get('Content-Type') || 'application/json',
    });

    if (request.headers.has('Authorization')) {
      headers.set(
        'Authorization',
        request.headers.get('Authorization') as string
      );
    } else if (session?.accessToken) {
      headers.set('Authorization', `Bearer ${session.accessToken}`);
    }

    const body = await cloneRequestBody(request);

    const requestOptions: any = {
      method,
      headers,
      body,
      credentials: 'omit',
      duplex: 'half',
    };

    let response = await fetch(proxyUrl, requestOptions);

    if (response.status === 401 && cookies.get('sessionId')) {
      const sessionId = cookies.get('sessionId') as string;

      if (!refreshRequests.has(sessionId)) {
        refreshRequests.set(sessionId, Auth.refresh({ cookies, fetch }));
      }

      const tokens = await refreshRequests.get(sessionId);
      refreshRequests.delete(sessionId);

      if (tokens) {
        const { accessToken, refreshToken } = tokens;

        if (accessToken) {
          requestOptions.headers.set('Authorization', `Bearer ${accessToken}`);
        }

        if (refreshToken && cookies.get('refreshToken') !== refreshToken) {
          cookies.set('refreshToken', refreshToken, {
            sameSite: 'strict',
            path: '/',
          });
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
