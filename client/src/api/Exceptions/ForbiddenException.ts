import { HttpException } from '@slink/api/Exceptions/HttpException';

export class ForbiddenException extends HttpException {
  constructor() {
    super('You do not have permission to access this resource', 403);
  }
}
