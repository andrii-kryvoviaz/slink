import { HttpException } from '@slink/api/Exceptions/HttpException';

export class UnauthorizedException extends HttpException {
  constructor() {
    super('You must to login first', 401);
  }
}
