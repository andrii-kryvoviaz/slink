import type { Cookies } from '@sveltejs/kit';

import { ApiClient } from '@slink/api/Client';
import { ValidationException } from '@slink/api/Exceptions';

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
  fetch?: typeof fetch;
};

export class Auth {
  private constructor() {}

  private static async _authenticateUser({
    accessToken,
    refreshToken,
    cookies,
  }: TokenPair & AuthDependencies) {
    cookies.set('refreshToken', refreshToken, {
      sameSite: 'strict',
      path: '/',
    });

    const response = await ApiClient.user.getCurrentUser(accessToken);
    const claims = parseJwt<{ roles: string[] }>(accessToken);

    const user = {
      id: response.id,
      email: response.email,
      displayName: response.displayName,
      username: response.username,
      roles: claims.roles,
    };

    const sessionId = await Session.create(cookies);
    await Session.set(sessionId, { user, accessToken });

    return user;
  }

  public static async login(
    { username, password }: Credentials,
    { cookies, fetch }: AuthDependencies,
  ) {
    if (fetch) ApiClient.use(fetch);

    const response = await ApiClient.auth.login(username, password);
    const { access_token, refresh_token } = response;

    await Session.destroy(cookies);

    const user = await this._authenticateUser({
      accessToken: access_token,
      refreshToken: refresh_token,
      cookies,
    });

    cookies.delete('createdUserId', {
      sameSite: 'strict',
      path: '/',
    });

    return user;
  }

  public static async refresh({
    cookies,
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
      });

      return {
        accessToken: access_token,
        refreshToken: refresh_token,
      };
    } catch (e) {
      if (e instanceof ValidationException) {
        Auth.logout({ cookies, fetch });
      }

      return;
    }
  }

  public static async logout({ cookies, fetch }: AuthDependencies) {
    if (fetch) ApiClient.use(fetch);

    const refreshToken = cookies.get('refreshToken');
    const sessionId = cookies.get('sessionId');

    if (!refreshToken || !sessionId) {
      return;
    }

    cookies.delete('refreshToken', {
      sameSite: 'strict',
      path: '/',
    });

    Session.destroy(cookies);

    try {
      await ApiClient.auth.logout(refreshToken);
    } catch (e) {
      console.warn('Refresh token has already been invalidated');
    }
  }
}
