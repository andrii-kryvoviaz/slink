import type { Cookies } from '@sveltejs/kit';

import { ApiClient } from '@slink/api/Client';

import type { CookieManager } from '@slink/lib/auth/CookieManager';
import { Session } from '@slink/lib/auth/Session';
import { parseJwt } from '@slink/lib/auth/parseJwt';

type TokenPair = {
  accessToken: string;
  refreshToken: string;
};

type Credentials = {
  username: string;
  password: string;
};

type AuthDependencies = {
  cookies: Cookies;
  cookieManager: CookieManager;
  fetch?: typeof fetch;
};

export class Auth {
  private constructor() {}

  private static async _authenticateUser({
    accessToken,
    refreshToken,
    cookies,
    cookieManager,
  }: TokenPair & AuthDependencies) {
    cookieManager.setCookie(cookies, 'refreshToken', refreshToken);

    const response = await ApiClient.user.getCurrentUser(accessToken);
    const claims = parseJwt<{ roles: string[] }>(accessToken);

    const user = {
      id: response.id,
      email: response.email,
      displayName: response.displayName,
      username: response.username,
      roles: claims.roles,
    };

    const sessionId = await Session.create(cookies, cookieManager);
    await Session.set(sessionId, { user, accessToken });

    return user;
  }

  public static async login(
    { username, password }: Credentials,
    { cookies, cookieManager, fetch }: AuthDependencies,
  ) {
    if (fetch) ApiClient.use(fetch);

    const response = await ApiClient.auth.login(username, password);
    const { access_token, refresh_token } = response;

    await Session.destroy(cookies, cookieManager);

    const user = await this._authenticateUser({
      accessToken: access_token,
      refreshToken: refresh_token,
      cookies,
      cookieManager,
    });

    cookieManager.deleteCookie(cookies, 'createdUserId');

    return user;
  }

  public static async refresh({
    cookies,
    cookieManager,
    fetch,
  }: AuthDependencies): Promise<TokenPair | undefined> {
    if (fetch) ApiClient.use(fetch);

    const refreshToken = cookies.get('refreshToken');

    if (!refreshToken) {
      return;
    }

    try {
      const response = await ApiClient.auth.refresh(refreshToken);
      const { access_token, refresh_token } = response;

      await this._authenticateUser({
        accessToken: access_token,
        refreshToken: refresh_token,
        cookies,
        cookieManager,
      });

      return {
        accessToken: access_token,
        refreshToken: refresh_token,
      };
    } catch {
      await Auth.logout({ cookies, cookieManager, fetch });
      return;
    }
  }

  public static async logout({
    cookies,
    cookieManager,
    fetch,
  }: AuthDependencies) {
    if (fetch) ApiClient.use(fetch);

    const refreshToken = cookies.get('refreshToken');
    const sessionId = cookies.get('sessionId');

    if (!refreshToken || !sessionId) {
      return;
    }

    cookieManager.deleteCookie(cookies, 'refreshToken');

    Session.destroy(cookies, cookieManager);

    try {
      await ApiClient.auth.logout(refreshToken);
    } catch {
      console.warn('Refresh token has already been invalidated');
    }
  }
}
