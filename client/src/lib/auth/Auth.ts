import { Session } from '@slink/lib/auth/Session';
import { parseJwt } from '@slink/lib/auth/parseJwt';
import type { Cookies } from '@sveltejs/kit';

import { ApiClient } from '@slink/api/Client';

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
      secure: true,
    });

    const claims = parseJwt<{ roles: string[] }>(accessToken);
    console.log('Claims', claims);

    const sessionId = Session.create(cookies);

    Session.set(sessionId, { accessToken });

    const response = await ApiClient.user.getCurrentUser();

    const user = {
      id: response.id,
      email: response.email,
      displayName: response.display_name,
      roles: claims.roles,
    };

    Session.set(sessionId, { user });

    return user;
  }

  public static async login(
    { username, password }: Credentials,
    { cookies, fetch }: AuthDependencies
  ) {
    if (fetch) ApiClient.use(fetch);

    const response = await ApiClient.auth.login(username, password);
    const { access_token, refresh_token } = response;

    Session.destroy(cookies);

    const user = await this._authenticateUser({
      accessToken: access_token,
      refreshToken: refresh_token,
      cookies,
    });

    cookies.delete('createdUserId', {
      sameSite: 'strict',
      path: '/',
      secure: true,
    });

    return user;
  }

  public static async refresh({ cookies, fetch }: AuthDependencies) {
    if (fetch) ApiClient.use(fetch);

    const refreshToken = cookies.get('refreshToken');

    if (!refreshToken) {
      return;
    }

    const response = await ApiClient.auth.refresh(refreshToken);
    const { access_token, refresh_token } = response;

    return this._authenticateUser({
      accessToken: access_token,
      refreshToken: refresh_token,
      cookies,
    });
  }

  public static async logout(cookies: Cookies) {
    cookies.delete('refreshToken', {
      sameSite: 'strict',
      path: '/',
      secure: true,
    });

    Session.destroy(cookies);
  }
}
