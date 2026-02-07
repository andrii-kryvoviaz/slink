import { ApiClient } from '@slink/api/Client';
import type { UserListingItem } from '@slink/api/Response';

import { AbstractPaginatedFeed } from '@slink/lib/state/core/AbstractPaginatedFeed.svelte';
import type {
  LoadParams,
  PaginatedResponse,
  SearchParams,
} from '@slink/lib/state/core/AbstractPaginatedFeed.svelte';
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

  public setAppendMode(mode: 'auto' | 'always' | 'never'): void {
    this._config.appendMode = mode;
  }

  public async loadPage(
    page: number,
    shouldAppend: boolean = false,
    limit?: number,
  ): Promise<void> {
    const currentMode = this._config.appendMode;

    if (!shouldAppend) {
      this._config.appendMode = 'never';
    }

    try {
      await this.load({ page, limit: limit ?? this._meta.size });
    } finally {
      this._config.appendMode = currentMode;
    }
  }

  public override async load(
    params: LoadParams & SearchParams = {},
    options?: Parameters<typeof this.fetch>[2],
  ): Promise<void> {
    const { page = this._meta.page, limit = this._meta.size } = params;

    if (this.isDirty && page === this._meta.page && limit === this._meta.size) {
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
