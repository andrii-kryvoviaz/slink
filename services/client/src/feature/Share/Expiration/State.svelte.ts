import { ApiClient } from '@slink/api';

import {
  datesEqual,
  daysUntil,
  narrowUnit,
  relativeFromDays,
  todayPlusDays,
} from '$lib/utils/date.svelte';
import { bindRequestState } from '$lib/utils/store/bindRequestState.svelte';

import { ReactiveState } from '@slink/api/ReactiveState';

import { shareMessages } from '../share.language';
import type { ShareStatusKind } from '../share.theme';

export interface ShareExpirationConfig {
  getShareId: () => string | null;
}

export interface ShareExpirationStatus {
  kind: ShareStatusKind;
  message: string;
}

export class ShareExpirationState {
  static readonly PRESET_DAYS = [1, 7, 30] as const;

  private _config: ShareExpirationConfig;

  private _enabled: boolean = $state(false);
  private _date: Date | null = $state(null);
  private _initial: Date | null = $state(null);
  private _pendingTarget: Date | null = $state(null);
  private _wasSaving: boolean = false;
  private _hasSavedOnce: boolean = $state(false);
  private _currentShareId: string | null = null;

  private _save = bindRequestState<void>(
    ReactiveState<void>(
      (shareId: string, expiresAt: Date | null) =>
        ApiClient.share.setExpiration(shareId, expiresAt),
      { debounce: 400 },
    ),
  );

  constructor(config: ShareExpirationConfig) {
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

      this._initial = this._pendingTarget;
      this._hasSavedOnce = true;
    });

    $effect(() => {
      return () => {
        this._save.dispose();
      };
    });
  }

  private _scheduleSave(value: Date | null): void {
    const shareId = this._config.getShareId();

    if (shareId === null) {
      return;
    }

    this._pendingTarget = value;
    void this._save.run(shareId, value);
  }

  private get _days(): number | null {
    if (!this._enabled || this._date === null) {
      return null;
    }

    return daysUntil(this._date);
  }

  get enabled(): boolean {
    return this._enabled;
  }

  get date(): Date | null {
    return this._date;
  }

  set date(value: Date | null) {
    if (datesEqual(value, this._date)) {
      return;
    }

    this._date = value;

    if (!this._enabled) {
      return;
    }

    this._scheduleSave(value);
  }

  get isSaving(): boolean {
    return this._save.isLoading;
  }

  get status(): ShareExpirationStatus | null {
    if (this._save.isLoading) {
      return { kind: 'saving', message: shareMessages.status.saving() };
    }

    if (this._save.error) {
      return { kind: 'error', message: shareMessages.status.error() };
    }

    if (!this._hasSavedOnce) {
      return null;
    }

    if (!datesEqual(this._date, this._initial)) {
      return null;
    }

    if (!this._enabled && this._date === null) {
      return null;
    }

    return { kind: 'saved', message: shareMessages.status.saved() };
  }

  get activePresetDays(): number | null {
    const days = this._days;

    if (days === null) {
      return null;
    }

    return ShareExpirationState.PRESET_DAYS.find((d) => d === days) ?? null;
  }

  get isExpired(): boolean {
    const days = this._days;

    return days !== null && days < 0;
  }

  get description(): string | null {
    const days = this._days;

    if (days === null) {
      return null;
    }

    if (days < 0) {
      return shareMessages.expirationDescription.expired();
    }

    if (days === 0) {
      return shareMessages.expirationDescription.today();
    }

    return shareMessages.expirationDescription.inFuture(relativeFromDays(days));
  }

  get descriptionShort(): string | null {
    const days = this._days;

    if (days === null) {
      return null;
    }

    if (days < 0) {
      return shareMessages.expirationShort.expired();
    }

    if (days === 0) {
      return shareMessages.expirationShort.today();
    }

    if (days < 7) {
      return narrowUnit(days, 'day');
    }

    if (days < 30) {
      return narrowUnit(Math.floor(days / 7), 'week');
    }

    if (days < 365) {
      return narrowUnit(Math.floor(days / 30), 'month');
    }

    return narrowUnit(Math.floor(days / 365), 'year');
  }

  setFromDays(days: number): void {
    this.date = todayPlusDays(days);
  }

  toggle = (enabled: boolean): void => {
    if (enabled === this._enabled) {
      return;
    }

    this._enabled = enabled;

    if (enabled) {
      return;
    }

    if (this._date === null) {
      return;
    }

    this._date = null;
    this._scheduleSave(null);
  };

  rebindTo(shareId: string | null, expiresAt: Date | null): void {
    if (shareId === this._currentShareId) {
      return;
    }

    if (this._save.isLoading) {
      return;
    }

    this._currentShareId = shareId;
    this._pendingTarget = null;
    this._hasSavedOnce = false;
    this._wasSaving = false;

    if (expiresAt === null) {
      this._enabled = false;
      this._date = null;
      this._initial = null;
      return;
    }

    this._enabled = true;
    this._date = expiresAt;
    this._initial = expiresAt;
  }

  hasPending(): boolean {
    return (
      this._enabled &&
      this._date !== null &&
      !datesEqual(this._date, this._initial)
    );
  }

  async applyPending(shareId: string): Promise<void> {
    if (!this.hasPending()) {
      return;
    }

    try {
      await ApiClient.share.setExpiration(shareId, this._date);
      this._initial = this._date;
      this._hasSavedOnce = true;
    } catch (e) {
      console.error(e);
    }
  }
}
