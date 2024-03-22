import { HttpException } from '@slink/api/Exceptions/HttpException';

export class NotFoundException extends HttpException {
  constructor() {
    super('Resource not found', 404);
  }
}
