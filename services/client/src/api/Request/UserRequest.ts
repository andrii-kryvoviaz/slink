import type { UserPreferencesResponse } from '@slink/api/Response/User/UserPreferencesResponse';

type UserSort = 'createdAt' | 'updatedAt' | 'displayName' | 'email' | 'status';

export type UserListFilter = {
  limit?: number;
  orderBy?: UserSort;
  order?: 'asc' | 'desc';
  searchTerm?: string | null;
};

export type UserPreferencesPatch = Partial<UserPreferencesResponse> & {
  'license.syncToImages'?: boolean;
};
