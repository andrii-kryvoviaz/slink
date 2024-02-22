import { HttpException } from '@slink/api/Exceptions/HttpException';

export class BadRequestException extends HttpException {
  constructor(Response: any, status: number = 400) {
    super('Bad Request', status);
  }
}
