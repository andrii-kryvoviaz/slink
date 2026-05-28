import { HttpException } from '@slink/api/Exceptions/HttpException';

export interface LockedExceptionPayload {
  requiresPassword?: boolean;
  shareId?: string;
  shortCode?: string;
}

export class LockedException extends HttpException {
  public readonly requiresPassword: boolean;
  public readonly shareId: string | null;
  public readonly shortCode: string | null;

  constructor(payload: LockedExceptionPayload = {}) {
    super('This resource is locked', 423);
    this.requiresPassword = payload.requiresPassword ?? true;
    this.shareId = payload.shareId ?? null;
    this.shortCode = payload.shortCode ?? null;
  }
}
