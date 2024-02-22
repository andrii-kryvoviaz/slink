import { HttpException } from '@slink/api/Exceptions/HttpException';

export class ForbiddenException extends HttpException {
  constructor(status = 403) {
    super('You do not have permission to access this resource', status);
  }
}
