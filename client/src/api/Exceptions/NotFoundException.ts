import { HttpException } from '@slink/api/Exceptions/HttpException';

export class NotFoundException extends HttpException {
  constructor(status = 404) {
    super('Resource not found', status);
  }
}
