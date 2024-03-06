import { HttpException } from '@slink/api/Exceptions/HttpException';

export class UnauthorizedException extends HttpException {
  constructor(status: number = 401) {
    super('You must to login first', status);
  }
}
