import type { Cookies } from '@sveltejs/kit';

import { type ApiClientType, createApiClient } from '@slink/api/Client';

import type { CookieManager } from '@slink/lib/auth/CookieManager';
import { Session } from '@slink/lib/auth/Session';
import type { TokenPair } from '@slink/lib/auth/Type/TokenPair';
import { parseJwt } from '@slink/lib/auth/parseJwt';

type Credentials = {
  username: string;
  password: string;
};

type AuthDependencies = {
  cookies: Cookies;
  cookieManager: CookieManager;
  fetch: typeof fetch;
};

export class Auth {
  private constructor() {}

  private static async _createSession(
    {
      accessToken,
      refreshToken,
      cookies,
      cookieManager,
    }: TokenPair & Omit<AuthDependencies, 'fetch'>,
    api: Pick<ApiClientType, 'user'>,
  ) {
    cookieManager.setCookie(cookies, 'refreshToken', refreshToken);

    const response = await api.user.getCurrentUser(accessToken);
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

  private static async _refreshSession({
    accessToken,
    refreshToken,
    cookies,
    cookieManager,
  }: TokenPair & Omit<AuthDependencies, 'fetch'>) {
    cookieManager.setCookie(cookies, 'refreshToken', refreshToken);

    const sessionId = await Session.create(cookies, cookieManager);
    await Session.set(sessionId, { accessToken });
  }

  public static async loginWithTokens(
    { accessToken, refreshToken }: TokenPair,
    { cookies, cookieManager, fetch }: AuthDependencies,
  ) {
    const api = createApiClient(fetch);

    await Session.destroy(cookies, cookieManager);

    const user = await this._createSession(
      { accessToken, refreshToken, cookies, cookieManager },
      api,
    );

    cookieManager.deleteCookie(cookies, 'createdUserId');

    return user;
  }

  public static async login(
    { username, password }: Credentials,
    { cookies, cookieManager, fetch }: AuthDependencies,
  ) {
    const api = createApiClient(fetch);
    const { access_token, refresh_token } = await api.auth.login(
      username,
      password,
    );

    return this.loginWithTokens(
      { accessToken: access_token, refreshToken: refresh_token },
      { cookies, cookieManager, fetch },
    );
  }

  public static async refresh({
    cookies,
    cookieManager,
    fetch,
  }: AuthDependencies): Promise<TokenPair | undefined> {
    const api = createApiClient(fetch);

    const refreshToken = cookies.get('refreshToken');

    if (!refreshToken) {
      return;
    }

    try {
      const response = await api.auth.refresh(refreshToken);
      const { access_token, refresh_token } = response;

      await this._refreshSession({
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
    const api = createApiClient(fetch);

    const refreshToken = cookies.get('refreshToken');
    const sessionId = cookies.get('sessionId');

    if (!refreshToken || !sessionId) {
      return;
    }

    cookieManager.deleteCookie(cookies, 'refreshToken');

    await Session.destroy(cookies, cookieManager);

    try {
      await api.auth.logout(refreshToken);
    } catch {
      console.warn('Refresh token has already been invalidated');
    }
  }
}
