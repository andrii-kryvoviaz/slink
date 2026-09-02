import { ApiClient } from '@slink/api';

import type { StorageUsageResponse } from '@slink/api/Resources/StorageResource';

import { AbstractHttpState } from '@slink/lib/state/core/AbstractHttpState.svelte';
import type { RequestStateOptions } from '@slink/lib/state/core/AbstractHttpState.svelte';
import { useState } from '@slink/lib/state/core/ContextAwareState';

class StorageUsageStore extends AbstractHttpState<StorageUsageResponse> {
  private _usage: StorageUsageResponse | null = $state(null);
  private _lastUpdated: Date | null = $state(null);
  private _isDisabled: boolean = $state(false);

  public constructor() {
    super();
  }

  public get usage(): StorageUsageResponse | null {
    return this._usage;
  }

  public get lastUpdated(): Date | null {
    return this._lastUpdated;
  }

  public get isEmpty(): boolean {
    return this._usage === null;
  }

  public get isDisabled(): boolean {
    return this._isDisabled;
  }

  public async load(options: RequestStateOptions = {}): Promise<void> {
    if (this._usage && !this.isDirty) {
      return;
    }

    await this.fetch(
      () => ApiClient.storage.getUsage(),
      (data) => {
        this._isDisabled = !data;
        if (!data) {
          return;
        }
        this._usage = data;
        this._lastUpdated = new Date();
      },
      options,
    );
  }

  public async refresh(options: RequestStateOptions = {}): Promise<void> {
    this.markDirty(true);
    await this.load(options);
  }

  public formatBytes(bytes: number): string {
    if (bytes === 0) return '0 B';

    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));

    return `${parseFloat((bytes / Math.pow(k, i)).toFixed(1))} ${sizes[i]}`;
  }

  public destroy(): void {}
}

const STORAGE_USAGE_STORE = Symbol('StorageUsageStore');

const storageUsageStore = new StorageUsageStore();

export const useStorageUsageStore = (
  func: ((store: StorageUsageStore) => void) | undefined = undefined,
): StorageUsageStore => {
  if (func) {
    func(storageUsageStore);
  }

  return useState<StorageUsageStore>(STORAGE_USAGE_STORE, storageUsageStore);
};
