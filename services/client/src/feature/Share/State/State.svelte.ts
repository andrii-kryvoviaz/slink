import { ShareExpirationState } from '@slink/feature/Share';

import { bindRequestState } from '$lib/utils/store/bindRequestState.svelte';
import { printErrorsAsToastMessage } from '$lib/utils/ui/printErrorsAsToastMessage';
import { routes } from '$lib/utils/url/routes';

import { ReactiveState } from '@slink/api/ReactiveState';
import type { ShareResponse } from '@slink/api/Response';

export interface ShareStateConfig {
  fetchShare: () => Promise<ShareResponse>;
  onEnsurePublished?: (shareId: string) => Promise<void>;
  initial?: ShareResponse | null;
}

export class ShareState {
  private _config: ShareStateConfig;

  private _shareId: string | null = $state(null);
  private _shareUrl: string | null = $state(null);

  private _request = bindRequestState<ShareResponse>(
    ReactiveState<ShareResponse>(() => this._config.fetchShare(), {
      minExecutionTime: 200,
      debounce: 300,
    }),
  );

  private _expiration: ShareExpirationState;

  constructor(config: ShareStateConfig) {
    this._config = config;

    this._expiration = new ShareExpirationState({
      getShareId: () => this._shareId,
    });

    $effect(() => {
      if (this._request.data) {
        this._apply(this._request.data);
      }
    });

    $effect(() => {
      if (this._request.error) {
        printErrorsAsToastMessage(this._request.error);
      }
    });

    $effect(() => {
      return () => {
        this._request.dispose();
      };
    });
  }

  private _apply(response: ShareResponse): void {
    const expiresAt = response.expiresAt ? new Date(response.expiresAt) : null;
    this._shareId = response.shareId;
    this._shareUrl = routes.share.fromResponse(response);
    this._expiration.rebindTo(response.shareId, expiresAt);
  }

  get shareUrl(): string | null {
    return this._shareUrl;
  }

  get isLoading(): boolean {
    return this._request.isLoading;
  }

  get isInitialized(): boolean {
    return this._shareId !== null;
  }

  get expiration(): ShareExpirationState {
    return this._expiration;
  }

  load = async (): Promise<void> => {
    await this._request.run();
  };

  hydrate = (response: ShareResponse): void => {
    this._apply(response);
  };

  ensurePublished = async (): Promise<string | void> => {
    if (!this.isInitialized) {
      await this.load();
    }

    const shareId = this._shareId;

    if (shareId === null) {
      return;
    }

    await this._expiration.applyPending(shareId);
    await this._config.onEnsurePublished?.(shareId);

    return this._shareUrl ?? undefined;
  };
}

export function createShare(config: ShareStateConfig): ShareState {
  const share = new ShareState(config);

  if (config.initial) {
    share.hydrate(config.initial);
  }

  return share;
}
