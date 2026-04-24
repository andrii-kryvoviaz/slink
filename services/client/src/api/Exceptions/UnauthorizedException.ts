import { HttpException } from '@slink/api/Exceptions/HttpException';

export class UnauthorizedException extends HttpException {
  constructor() {
    super('You must log in first', 401);
  }
}
