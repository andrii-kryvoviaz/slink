import { HttpException } from '@slink/api/Exceptions/HttpException';

export class ConflictException extends HttpException {
  constructor() {
    super('This was changed by someone else. Reload and try again.', 409);
  }
}
