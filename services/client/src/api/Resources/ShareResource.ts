import { AbstractResource } from '@slink/api/AbstractResource';

export class ShareResource extends AbstractResource {
  public async setExpiration(
    shareId: string,
    expiresAt: Date | null,
  ): Promise<void> {
    return this.put(`/share/${shareId}/expiration`, {
      json: { expiresAt: expiresAt?.toISOString() ?? null },
    });
  }

  public async setPassword(
    shareId: string,
    password: string | null,
  ): Promise<void> {
    return this.put(`/share/${shareId}/password`, {
      json: { password },
    });
  }

  public async unlock(shareId: string, password: string): Promise<void> {
    return this.post(`/share/${shareId}/unlock`, {
      json: { password },
    });
  }
}
