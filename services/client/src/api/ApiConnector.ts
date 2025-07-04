import type { Handle } from '@sveltejs/kit';

import { Session } from '@slink/lib/auth/Session';

import { getResponseWithCookies } from '@slink/utils/http/cookie';

import { ApiRequestBuilder } from './ApiRequestBuilder';
import { TokenRefreshManager } from './TokenRefreshManager';

type ApiOptions = {
  baseUrl: string;
  urlPrefix: string;
  registeredPaths: string[];
};

export const ApiConnector = (options: ApiOptions): Handle => {
  const tokenManager = TokenRefreshManager.getInstance();
  return async ({ event, resolve }) => {
    const { url, fetch, cookies, locals } = event;
    const session = await Session.get(cookies.get('sessionId'));

    const pathRegex = new RegExp(`^(${options.registeredPaths.join('|')})`);

    if (!pathRegex.test(url.pathname)) {
      if (session?.user) {
        locals.user = session.user;
      }
      return resolve(event);
    }

    const requestBuilder = new ApiRequestBuilder(
      event,
      options.baseUrl,
      options.urlPrefix,
    );
    const proxyUrl = requestBuilder.buildProxyUrl();

    const makeRequest = async (accessToken?: string): Promise<Response> => {
      const requestOptions =
        await requestBuilder.buildRequestOptions(accessToken);
      return fetch(proxyUrl, requestOptions);
    };

    let response = await makeRequest(session?.accessToken);
    let authRefreshed = false;

    if (response.status === 401 && cookies.get('sessionId')) {
      const sessionId = cookies.get('sessionId') as string;

      try {
        response = await tokenManager.handleTokenRefresh(
          sessionId,
          { cookies, fetch },
          makeRequest,
        );
        authRefreshed = true;
      } catch (error) {
        console.warn('Token refresh failed:', error);
      }
    }

    return getResponseWithCookies({ response, cookies, authRefreshed });
  };
};
