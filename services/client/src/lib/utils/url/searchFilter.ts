export const searchByValues = ['user', 'description', 'hashtag'] as const;

export type SearchBy = (typeof searchByValues)[number];

export interface SearchFilter {
  searchTerm?: string;
  searchBy?: SearchBy;
}

export const resolveSearchBy = (value: unknown): SearchBy => {
  return searchByValues.find((candidate) => candidate === value) ?? 'user';
};
