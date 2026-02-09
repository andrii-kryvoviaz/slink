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

export const ApiProxy = (options: ApiOptions): Handle => {
  const tokenManager = TokenRefreshManager.getInstance();
  return async ({ event, resolve }) => {
    const { url, fetch, cookies, locals } = event;
    const { globalSettings } = locals;

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

    let response = await makeRequest(session?.accessToken as string);
    let authRefreshed = false;

    if (response.status === 401 && cookies.get('sessionId')) {
      const sessionId = cookies.get('sessionId') as string;

      try {
        const result = await tokenManager.handleTokenRefresh(
          sessionId,
          { cookies, cookieManager: locals.cookieManager, fetch },
          makeRequest,
        );
        response = result.response;
        authRefreshed = result.tokensRefreshed;
      } catch (error) {
        console.warn('Token refresh failed:', error);

        locals.cookieManager.deleteCookie(cookies, 'refreshToken');
        locals.cookieManager.deleteCookie(cookies, 'sessionId');

        return getResponseWithCookies({
          response: new Response(null, {
            status: 302,
            headers: { Location: '/profile/login' },
          }),
          cookies,
          requireSsl: globalSettings?.access?.requireSsl ?? false,
          authRefreshed: true,
        });
      }
    }

    return getResponseWithCookies({
      response,
      cookies,
      requireSsl: globalSettings?.access?.requireSsl ?? false,
      authRefreshed,
    });
  };
};
