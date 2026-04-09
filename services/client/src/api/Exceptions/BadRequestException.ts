import { HttpException } from '@slink/api/Exceptions/HttpException';

export class BadRequestException extends HttpException {
  public readonly params?: Record<string, string | number>;

  constructor(response: {
    message: string;
    title: string;
    params?: Record<string, string | number>;
  }) {
    const message = response?.message || 'Bad Request';
    super(message, 400);
    this.params = response?.params;
  }
}
