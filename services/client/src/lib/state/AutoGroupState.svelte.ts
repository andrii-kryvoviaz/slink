import { ApiClient } from '@slink/api';

import type { CollectionResponse } from '@slink/api/Response';

export class AutoGroupState {
  private _enabled: boolean = $state(true);
  private _pending: boolean = $state(false);
  private _createdCollection: CollectionResponse | null = $state(null);
  private _failed: boolean = $state(false);
  private _undoPending: boolean = $state(false);

  constructor(enabled: boolean) {
    this._enabled = enabled;
  }

  get enabled(): boolean {
    return this._enabled;
  }

  get pending(): boolean {
    return this._pending;
  }

  get createdCollection(): CollectionResponse | null {
    return this._createdCollection;
  }

  get failed(): boolean {
    return this._failed;
  }

  get undoPending(): boolean {
    return this._undoPending;
  }

  async setEnabled(value: boolean): Promise<void> {
    const previous = this._enabled;
    this._enabled = value;
    this._pending = true;

    try {
      await ApiClient.user.updatePreferences({ autoGroupBatchUploads: value });
    } catch (error) {
      console.error('Failed to update auto group preference:', error);
      this._enabled = previous;
    } finally {
      this._pending = false;
    }
  }

  async create(): Promise<CollectionResponse | undefined> {
    try {
      const collection = await ApiClient.collection.create({
        name: 'Unnamed',
      });
      this._createdCollection = collection;
      this._failed = false;
      return collection;
    } catch (error) {
      console.error('Failed to create unnamed collection:', error);
      this._failed = true;
      return undefined;
    }
  }

  async undo(): Promise<boolean> {
    if (!this._createdCollection) return false;

    this._undoPending = true;

    try {
      await this._removeCreated();
      return true;
    } catch (error) {
      console.error('Failed to undo auto collection:', error);
      return false;
    } finally {
      this._undoPending = false;
    }
  }

  async discardCreated(): Promise<void> {
    if (!this._createdCollection) return;

    try {
      await this._removeCreated();
    } catch (error) {
      console.error('Failed to discard auto collection:', error);
    }
  }

  private async _removeCreated(): Promise<void> {
    if (!this._createdCollection) return;

    await ApiClient.collection.remove(this._createdCollection.id, false);
    this._createdCollection = null;
  }

  reset(): void {
    this._pending = false;
    this._createdCollection = null;
    this._failed = false;
    this._undoPending = false;
  }
}

export function createAutoGroupState(enabled: boolean): AutoGroupState {
  return new AutoGroupState(enabled);
}
