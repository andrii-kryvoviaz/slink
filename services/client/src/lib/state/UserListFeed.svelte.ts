import { ApiClient } from '@slink/api';

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

  override get key() {
    return 'users' as const;
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

  public override async removeItems(
    ids: string[],
    options?: Parameters<typeof this.fetch>[2],
  ): Promise<void> {
    const previousCount = this.items.length;

    await super.removeItems(ids, options);

    if (this.items.length === previousCount) {
      return;
    }

    await this._refetchWindow();
  }

  private async _refetchWindow(): Promise<void> {
    const size = this._meta.size;
    const page = this.currentPage;

    this.invalidate();

    if (this.items.length >= size) {
      await this.load({ page: 1, limit: page * size });
      this._meta = { ...this._meta, page, size };
      return;
    }

    await this.loadPage(page, false, size);

    if (this.hasItems || page === 1) {
      return;
    }

    await this.loadPage(page - 1, false, size);
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
