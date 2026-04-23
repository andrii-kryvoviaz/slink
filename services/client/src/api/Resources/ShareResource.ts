import { AbstractResource } from '@slink/api/AbstractResource';
import type {
  ShareListQuery,
  ShareListingResponse,
} from '@slink/api/Response/Share/ShareListItemResponse';

export class ShareResource extends AbstractResource {
  public async list(
    params: ShareListQuery = {},
  ): Promise<ShareListingResponse> {
    return this.get('/shares', {
      query: params as Record<string, unknown>,
    });
  }

  public async unpublish(shareId: string): Promise<void> {
    return this.put(`/share/${shareId}/unpublish`);
  }

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
