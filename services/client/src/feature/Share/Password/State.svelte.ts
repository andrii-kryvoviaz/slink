import { ApiClient } from '@slink/api';

import { bindRequestState } from '$lib/utils/store/bindRequestState.svelte';

import { ReactiveState } from '@slink/api/ReactiveState';

import type { ShareStatusKind } from '../share.theme';

export interface SharePasswordConfig {
  getShareId: () => string | null;
  intent?: boolean;
}

export class SharePasswordState {
  static readonly MIN_LENGTH = 6;

  private _config: SharePasswordConfig;

  private _enabled: boolean = $state(false);
  private _password: string = $state('');
  private _pending: string | null = $state(null);
  private _initialEnabled: boolean = $state(false);
  private _wasSaving: boolean = $state.raw(false);
  private _hasSavedOnce: boolean = $state(false);
  private _lastTarget: string | null = null;
  private _currentShareId: string | null = null;

  private _save = bindRequestState<void>(
    ReactiveState<void>((shareId: string, password: string | null) =>
      ApiClient.share.setPassword(shareId, password),
    ),
  );

  constructor(config: SharePasswordConfig) {
    this._config = config;

    $effect(() => {
      const saving = this._save.isLoading;
      const error = this._save.error;

      if (saving) {
        this._wasSaving = true;
        return;
      }

      if (!this._wasSaving) {
        return;
      }

      this._wasSaving = false;

      if (error) {
        return;
      }

      this._initialEnabled = this._lastTarget !== null;
      this._hasSavedOnce = true;
      this._password = '';
    });

    $effect(() => {
      return () => {
        this._save.dispose();
      };
    });
  }

  get enabled(): boolean {
    return this._enabled;
  }

  get isProtected(): boolean {
    return this._initialEnabled;
  }

  get password(): string {
    return this._password;
  }

  get pendingPassword(): string | null {
    return this._pending;
  }

  get hasExistingPassword(): boolean {
    return this._initialEnabled && this._password.length === 0;
  }

  get isSaving(): boolean {
    return this._save.isLoading;
  }

  get minLength(): number {
    return SharePasswordState.MIN_LENGTH;
  }

  get isPasswordValid(): boolean {
    return this._password.length >= SharePasswordState.MIN_LENGTH;
  }

  get status(): ShareStatusKind | null {
    if (this._save.isLoading) {
      return 'saving';
    }

    if (this._save.error) {
      return 'error';
    }

    if (!this._hasSavedOnce) {
      return null;
    }

    return 'saved';
  }

  setPassword = (value: string): void => {
    if (value === this._password) {
      return;
    }

    this._password = value;
  };

  commit = async (): Promise<void> => {
    if (!this._enabled) {
      return;
    }

    if (!this.isPasswordValid) {
      return;
    }

    if (this._config.intent) {
      this._pending = this._password;
      this._initialEnabled = true;
      this._password = '';
      return;
    }

    const shareId = this._config.getShareId();

    if (shareId === null) {
      return;
    }

    this._lastTarget = this._password;
    await this._save.run(shareId, this._password);
  };

  toggle = (enabled: boolean): void => {
    if (enabled === this._enabled) {
      return;
    }

    this._enabled = enabled;

    if (enabled) {
      return;
    }

    this._password = '';

    if (!this._initialEnabled) {
      return;
    }

    if (this._config.intent) {
      this._pending = null;
      this._initialEnabled = false;
      return;
    }

    const shareId = this._config.getShareId();

    if (shareId === null) {
      return;
    }

    this._lastTarget = null;
    void this._save.run(shareId, null);
  };

  rebindTo(shareId: string | null, requiresPassword: boolean): void {
    if (shareId === this._currentShareId) {
      return;
    }

    if (this._save.isLoading) {
      return;
    }

    this._currentShareId = shareId;
    this._lastTarget = null;
    this._hasSavedOnce = false;
    this._wasSaving = false;
    this._password = '';
    this._enabled = requiresPassword;
    this._initialEnabled = requiresPassword;
  }
}
