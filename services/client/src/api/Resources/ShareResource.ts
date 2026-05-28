import { AbstractResource } from '@slink/api/AbstractResource';
import type {
  ShareExpiryFilter,
  ShareListQuery,
  ShareListingResponse,
  ShareProtectionFilter,
  ShareTypeFilter,
} from '@slink/api/Response/Share/ShareListItemResponse';
import type { ShareableType } from '@slink/api/Response/Share/ShareResponse';

export class ShareResource extends AbstractResource {
  public async list(
    params: ShareListQuery = {},
  ): Promise<ShareListingResponse> {
    return this.get('/shares', {
      query: params as Record<string, unknown>,
    });
  }

  public async exists(
    filters: {
      searchTerm?: string;
      expiry?: ShareExpiryFilter;
      protection?: ShareProtectionFilter;
      type?: ShareTypeFilter;
      shareableId?: string;
      shareableType?: ShareableType;
    } = {},
  ): Promise<boolean> {
    const response = await this.get<{ exists: boolean }>('/shares/exists', {
      query: filters as Record<string, unknown>,
    });
    return response.exists;
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
