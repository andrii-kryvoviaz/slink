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
}
