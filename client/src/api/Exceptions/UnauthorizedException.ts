import { HttpException } from '@slink/api/Exceptions/HttpException';

export class UnauthorizedException extends HttpException {
  constructor(status: number = 401) {
    super('Unauthorized', status);
  }
}
