type UserSort = 'createdAt' | 'updatedAt' | 'displayName' | 'email' | 'status';

export type UserListFilter = {
  limit?: number;
  orderBy?: UserSort;
  order?: 'asc' | 'desc';
  searchTerm?: string | null;
};
