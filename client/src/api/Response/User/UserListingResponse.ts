import type { UserRole } from '@slink/lib/auth/Type/UserRole';

import type { ListingMetadata } from '@slink/api/Response';

export type UserListingItem = {
  id: string;
  email: string;
  displayName: string;
  createdAt: {
    formattedDate: string;
    timestamp: number;
  };
  updatedAt: null;
  status: string;
  roles: UserRole[];
};

export type UserListingResponse = {
  meta: ListingMetadata;
  data: UserListingItem[];
};
