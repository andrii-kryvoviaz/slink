import { HttpException } from '@slink/api/Exceptions/HttpException';

export class BadRequestException extends HttpException {
  constructor(response: { message: string; title: string }) {
    const message = response?.message || 'Bad Request';
    super(message, 400);
  }
}
