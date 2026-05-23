import { ApiClient } from '@slink/api';

import type {
  TagListingResponse,
  TagOrderBy,
} from '@slink/api/Resources/TagResource';

import type { ViewMode } from '@slink/lib/settings';

export interface TagListQuery {
  page: number;
  limit: number;
  searchTerm: string;
  orderBy: TagOrderBy;
  order: 'asc' | 'desc';
}

export interface TagFetchStrategy {
  fetch(query: TagListQuery): Promise<TagListingResponse>;
}

const flatTagStrategy: TagFetchStrategy = {
  fetch: (query) =>
    ApiClient.tag.getList({
      page: query.page,
      limit: query.limit,
      includeChildren: true,
      searchTerm: query.searchTerm,
      orderBy: query.orderBy,
      order: query.order,
    }),
};

const rootsTagStrategy: TagFetchStrategy = {
  fetch: (query) =>
    ApiClient.tag.getList({
      includeChildren: false,
      searchTerm: query.searchTerm,
      orderBy: 'name',
      order: 'asc',
      ...(query.searchTerm ? {} : { rootOnly: true }),
    }),
};

export function tagStrategyFor(viewMode: ViewMode): TagFetchStrategy {
  if (viewMode === 'tree') {
    return rootsTagStrategy;
  }
  return flatTagStrategy;
}
