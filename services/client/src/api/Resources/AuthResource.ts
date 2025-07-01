import { AbstractResource } from '@slink/api/AbstractResource';
import type { EmptyResponse, ViolationResponse } from '@slink/api/Response';
import type { LoginResponse } from '@slink/api/Response/Auth/LoginResponse';
import type { SignupResponse } from '@slink/api/Response/Auth/SignupResponse';
import type { GenericError } from '@slink/api/Response/Error/GenericError';
import type { ResponseWithHeaders } from '@slink/api/Response/ResponseWithHeaders';

export class AuthResource extends AbstractResource {
  public async signup(data: {
    email: string;
    username: string;
    password: string;
    confirm: string;
  }): Promise<SignupResponse & ResponseWithHeaders> {
    return this.post('/auth/signup', {
      json: data,
      includeResponseHeaders: true,
    });
  }

  public async login(
    username: string,
    password: string,
  ): Promise<LoginResponse> {
    return this.post('/auth/login', {
      json: { username, password },
    });
  }

  public async refresh(
    refreshToken: string,
  ): Promise<LoginResponse & GenericError<ViolationResponse>> {
    return this.post('/auth/refresh', {
      json: { refresh_token: refreshToken },
    });
  }

  public async logout(refreshToken: string): Promise<EmptyResponse> {
    return this.post('/auth/logout', {
      json: { refresh_token: refreshToken },
    });
  }
}
