import { getContext, setContext } from 'svelte';

import type { UploadItem } from '@slink/lib/services/upload.service';

type Getter<T> = () => T;

export class ProgressState {
  private _items: Getter<UploadItem[]>;

  items = $derived.by(() => this._items());
  totalCount = $derived.by(() => this.items.length);
  completedCount = $derived.by(
    () => this.items.filter((item) => item.status === 'completed').length,
  );
  failedCount = $derived.by(
    () => this.items.filter((item) => item.status === 'error').length,
  );
  totalBytes = $derived.by(() =>
    this.items.reduce((sum, item) => sum + item.file.size, 0),
  );
  overallProgress = $derived.by(() => {
    if (this.totalBytes === 0) {
      return 0;
    }

    const uploadedBytes = this.items.reduce(
      (sum, item) => sum + (item.progress / 100) * item.file.size,
      0,
    );

    return (uploadedBytes / this.totalBytes) * 100;
  });
  isCompleted = $derived.by(
    () => this.completedCount + this.failedCount === this.totalCount,
  );
  hasErrors = $derived.by(() => this.failedCount > 0);

  constructor(items: Getter<UploadItem[]>) {
    this._items = items;
  }
}

const SYMBOL_KEY = 'slink-upload-progress';

export function setUploadProgress(items: Getter<UploadItem[]>): ProgressState {
  return setContext(Symbol.for(SYMBOL_KEY), new ProgressState(items));
}

export function useUploadProgress(): ProgressState {
  return getContext(Symbol.for(SYMBOL_KEY));
}
