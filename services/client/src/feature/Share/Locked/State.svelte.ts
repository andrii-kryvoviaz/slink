import { ApiClient } from '@slink/api';

import { bindRequestState } from '$lib/utils/store/bindRequestState.svelte';

import { BadRequestException } from '@slink/api/Exceptions/BadRequestException';
import { ReactiveState } from '@slink/api/ReactiveState';

import { messages } from '@slink/lib/utils/i18n/messages/toast.language';

import { toast } from '@slink/utils/ui/toast-sonner.svelte';

export interface ShareUnlockConfig {
  shareId: string;
  onFailure?: () => void;
}

export class ShareUnlockState {
  private _config: ShareUnlockConfig;

  private _password: string = $state('');
  private _revealed: boolean = $state(false);

  private _unlock = bindRequestState<void>(
    ReactiveState<void>((shareId: string, password: string) =>
      ApiClient.share.unlock(shareId, password),
    ),
  );

  constructor(config: ShareUnlockConfig) {
    this._config = config;

    $effect(() => {
      return () => {
        this._unlock.dispose();
      };
    });
  }

  get password(): string {
    return this._password;
  }

  set password(value: string) {
    this._password = value;
  }

  get revealed(): boolean {
    return this._revealed;
  }

  get isSubmitting(): boolean {
    return this._unlock.isLoading;
  }

  get isDisabled(): boolean {
    return this._password.length === 0 || this._unlock.isLoading;
  }

  toggleReveal = (): void => {
    this._revealed = !this._revealed;
  };

  submit = async (): Promise<void> => {
    if (this.isDisabled) {
      return;
    }

    await this._unlock.run(this._config.shareId, this._password);

    const error = this._unlock.error;

    if (error) {
      if (error instanceof BadRequestException) {
        toast.error(messages.share.locked.invalid);
      } else {
        toast.error(messages.share.locked.error);
      }

      this._password = '';
      this._config.onFailure?.();
      return;
    }

    window.location.reload();
  };
}

export function createShareUnlockState(
  config: ShareUnlockConfig,
): ShareUnlockState {
  return new ShareUnlockState(config);
}
