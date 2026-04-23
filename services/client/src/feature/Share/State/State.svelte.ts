import { ApiClient } from '@slink/api';
import {
  ShareExpirationState,
  SharePasswordState,
  type ShareStateRegistry,
} from '@slink/feature/Share';

import { bindRequestState } from '$lib/utils/store/bindRequestState.svelte';
import { copyText } from '$lib/utils/ui/clipboard';
import { printErrorsAsToastMessage } from '$lib/utils/ui/printErrorsAsToastMessage';
import { routes } from '$lib/utils/url/routes';

import { ReactiveState } from '@slink/api/ReactiveState';
import type { ShareResponse } from '@slink/api/Response';

export interface ShareStateConfig {
  fetchShare: () => Promise<ShareResponse>;
  onEnsurePublished?: (shareId: string) => Promise<void>;
  onUnpublished?: (shareId: string) => Promise<void> | void;
  initial?: ShareResponse | null;
  registry?: ShareStateRegistry | null;
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

  private _expiration: ShareExpirationState = $state.raw(
    new ShareExpirationState({ getShareId: () => this._shareId }),
  );
  private _password: SharePasswordState = $state.raw(
    new SharePasswordState({ getShareId: () => this._shareId }),
  );

  constructor(config: ShareStateConfig) {
    this._config = config;

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

    const registry = this._config.registry;

    if (registry) {
      this._expiration = registry.expiration(response.shareId, { expiresAt });
      this._password = registry.password(response.shareId, {
        requiresPassword: response.requiresPassword,
      });
      return;
    }

    this._expiration.rebindTo(response.shareId, expiresAt);
    this._password.rebindTo(response.shareId, response.requiresPassword);
  }

  get shareUrl(): string | null {
    return this._shareUrl;
  }

  get shareId(): string | null {
    return this._shareId;
  }

  clear = (): void => {
    this._shareId = null;
    this._shareUrl = null;
  };

  get isLoading(): boolean {
    return this._request.isLoading;
  }

  get isInitialized(): boolean {
    return this._shareId !== null;
  }

  get expiration(): ShareExpirationState {
    return this._expiration;
  }

  get password(): SharePasswordState {
    return this._password;
  }

  load = async (): Promise<void> => {
    await this._request.run();
  };

  hydrate = (response: ShareResponse): void => {
    this._apply(response);
  };

  hasPending(): boolean {
    return this._expiration.hasPending();
  }

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

  copy = async (): Promise<void> => {
    const url = await this.ensurePublished();

    if (!url) {
      return;
    }

    await copyText(url);
  };

  unpublish = async (): Promise<void> => {
    const shareId = this._shareId;

    if (shareId === null) {
      return;
    }

    try {
      await ApiClient.share.unpublish(shareId);
      this.clear();
      await this._config.onUnpublished?.(shareId);
    } catch (error: unknown) {
      printErrorsAsToastMessage(error as Error);
    }
  };
}

export function createShare(config: ShareStateConfig): ShareState {
  const share = new ShareState(config);

  if (config.initial) {
    share.hydrate(config.initial);
  }

  return share;
}
