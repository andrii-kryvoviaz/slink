export type SearchBy = 'user' | 'description' | 'hashtag';

export interface SearchFilter {
  searchTerm?: string;
  searchBy?: SearchBy;
}

export const resolveSearchFilter = (params: URLSearchParams): SearchFilter => {
  const searchTerm = (params.get('search') ?? '').trim();

  if (!searchTerm) {
    return {};
  }

  return {
    searchTerm,
    searchBy: (params.get('searchBy') ?? 'user') as SearchBy,
  };
};
