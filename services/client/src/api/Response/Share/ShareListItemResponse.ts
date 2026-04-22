import type { ListingMetadata } from '@slink/api/Response/Common/ListingMetadata';
import type { ShareableType } from '@slink/api/Response/Share/ShareResponse';

export type ShareExpiryFilter = 'any' | 'hasExpiry' | 'expired' | 'noExpiry';

export type ShareProtectionFilter = 'any' | 'passwordProtected' | 'noPassword';

export type ShareTypeFilter = 'all' | 'image' | 'collection';

export interface ShareListItemShareable {
  id: string;
  name: string;
  previewUrl: string | null;
  width?: number;
  height?: number;
  format?: string;
}

export interface ShareListItemVariant {
  width: number | null;
  height: number | null;
  filter: string | null;
  format: string | null;
}

export interface ShareListItemResponse {
  shareId: string;
  shareUrl: string;
  shortCode: string | null;
  type: ShareableType;
  isPublished: boolean;
  expiresAt: string | null;
  isExpired: boolean;
  requiresPassword: boolean;
  createdAt: string;
  shareable: ShareListItemShareable;
  variant: ShareListItemVariant;
}

export interface ShareListingResponse {
  data: ShareListItemResponse[];
  meta: ListingMetadata;
}

export interface ShareListQuery {
  limit?: number;
  cursor?: string;
  orderBy?: string;
  order?: 'asc' | 'desc';
  searchTerm?: string;
  expiry?: ShareExpiryFilter;
  protection?: ShareProtectionFilter;
  type?: ShareTypeFilter;
  shareableId?: string;
  shareableType?: ShareableType;
}
