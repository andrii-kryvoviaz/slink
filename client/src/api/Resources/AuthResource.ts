import { AbstractResource } from '@slink/api/AbstractResource';
import type { EmptyResponse } from '@slink/api/Response';
import type { LoginResponse } from '@slink/api/Response/Auth/LoginResponse';
import type { SignupResponse } from '@slink/api/Response/Auth/SignupResponse';
import type { ResponseWithHeaders } from '@slink/api/Response/ResponseWithHeaders';

export class AuthResource extends AbstractResource {
  public async signup(data: {
    email: string;
    display_name: string;
    password: string;
    confirm: string;
  }): Promise<SignupResponse & ResponseWithHeaders> {
    return this.post('/user', {
      json: data,
      ignoreAuth: true,
    });
  }

  public async login(
    username: string,
    password: string
  ): Promise<LoginResponse> {
    return this.post('/auth', {
      json: { username, password },
      ignoreAuth: true,
    });
  }

  public async refresh(refreshToken: string): Promise<LoginResponse> {
    return this.post('/refresh', {
      json: { refresh_token: refreshToken },
    });
  }

  public async logout(refreshToken: string): Promise<EmptyResponse> {
    return this.post('/logout', {
      json: { refresh_token: refreshToken },
    });
  }
}
