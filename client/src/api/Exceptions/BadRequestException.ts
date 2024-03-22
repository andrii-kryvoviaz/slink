import { HttpException } from '@slink/api/Exceptions/HttpException';

export class BadRequestException extends HttpException {
  constructor(Response: {
    error: {
      message: string;
      title: string;
    };
  }) {
    const message = Response?.error?.message || 'Bad Request';
    super(message, 400);
  }
}
