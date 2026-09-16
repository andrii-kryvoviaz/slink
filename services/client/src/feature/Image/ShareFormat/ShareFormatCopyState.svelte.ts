import { useAutoReset } from '$lib/utils/time/useAutoReset.svelte';

import type { ShareFormat } from '@slink/lib/settings';

type CopyWith = (format: ShareFormat) => Promise<boolean>;

export class ShareFormatCopyState {
  private readonly _copyWith: CopyWith;
  private readonly _copied: ReturnType<typeof useAutoReset>;
  private _copying = $state(false);

  constructor(copyWith: CopyWith, delay: number) {
    this._copyWith = copyWith;
    this._copied = useAutoReset(delay);
  }

  get copied(): boolean {
    return this._copied.active;
  }

  get copying(): boolean {
    return this._copying;
  }

  copy = async (format: ShareFormat): Promise<void> => {
    this._copying = true;

    try {
      if (await this._copyWith(format)) {
        this._copied.trigger();
      }
    } finally {
      this._copying = false;
    }
  };
}
