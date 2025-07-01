import { ApiClient } from '@slink/api/Client';
import type { UserListingItem } from '@slink/api/Response';

import type {
  LoadParams,
  PaginatedResponse,
  SearchParams,
} from '@slink/lib/state/core/AbstractPaginatedFeed.svelte';
import { AbstractPaginatedFeed } from '@slink/lib/state/core/AbstractPaginatedFeed.svelte';
import { useState } from '@slink/lib/state/core/ContextAwareState';

class UserListFeed extends AbstractPaginatedFeed<UserListingItem> {
  public constructor() {
    super({
      defaultPageSize: 12,
      useCursor: false,
      appendMode: 'always',
    });
  }

  protected async fetchData(
    params: LoadParams & SearchParams,
  ): Promise<PaginatedResponse<UserListingItem>> {
    const { page = 1, limit = 12 } = params;
    return ApiClient.user.getUsers(page, { limit });
  }

  protected _getItemId(item: UserListingItem): string {
    return item.id;
  }

  public removeUser(id: string): void {
    this._items = this._items.filter((item) => item.id !== id);
  }

  public override async load(
    params: LoadParams & SearchParams = {},
    options?: Parameters<typeof this.fetch>[2],
  ): Promise<void> {
    const { page = this._meta.page } = params;

    if (this.isDirty && page === this._meta.page) {
      return;
    }

    await super.load(params, options);
  }
}

const USER_LIST_FEED = Symbol('UserListFeed');

const userListFeed = new UserListFeed();

export const useUserListFeed = (
  func: ((state: UserListFeed) => void) | undefined = undefined,
): UserListFeed => {
  if (func) {
    func(userListFeed);
  }

  return useState<UserListFeed>(USER_LIST_FEED, userListFeed);
};
