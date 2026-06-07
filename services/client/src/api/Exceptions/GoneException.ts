import { HttpException } from '@slink/api/Exceptions/HttpException';

export class GoneException extends HttpException {
  constructor(response?: { title?: string; message?: string }) {
    super(response?.message || 'This resource is no longer available', 410);
  }
}
