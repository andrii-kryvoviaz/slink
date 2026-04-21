import { HttpException } from '@slink/api/Exceptions/HttpException';

export class BadRequestException extends HttpException {
  constructor(response?: { title?: string; message?: string }) {
    super(response?.message || 'Bad Request', 400);
  }
}
