import { HttpException } from '@slink/api/Exceptions/HttpException';

export class PayloadTooLargeException extends HttpException {
  constructor(response?: { message: string; title?: string }) {
    const message =
      response?.message ||
      'File too large. Please choose a smaller file and try again.';
    super(message, 413);
  }
}
