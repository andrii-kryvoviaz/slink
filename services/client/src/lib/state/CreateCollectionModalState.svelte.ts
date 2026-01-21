import { toast } from '$lib/utils/ui/toast-sonner.svelte.js';

import { ApiClient } from '@slink/api/Client';
import { ValidationException } from '@slink/api/Exceptions';
import type { CollectionResponse } from '@slink/api/Response';

export class CreateCollectionModalState {
  private _isOpen: boolean = $state(false);
  private _isSubmitting: boolean = $state(false);
  private _errors: Record<string, string> = $state({});
  private _onCreated: ((collection: CollectionResponse) => void) | null = null;

  get isOpen() {
    return this._isOpen;
  }

  get isSubmitting() {
    return this._isSubmitting;
  }

  get errors() {
    return this._errors;
  }

  open(onCreated?: (collection: CollectionResponse) => void) {
    this._isOpen = true;
    this._errors = {};
    this._onCreated = onCreated ?? null;
  }

  close() {
    this._isOpen = false;
    this._errors = {};
    this._onCreated = null;
  }

  async submit(data: { name: string; description?: string }): Promise<boolean> {
    this._isSubmitting = true;
    this._errors = {};

    try {
      const collection = await ApiClient.collection.create(data);
      this._onCreated?.(collection);
      this.close();
      return true;
    } catch (error) {
      if (error instanceof ValidationException && error.violations) {
        this._errors = error.violations.reduce<Record<string, string>>(
          (acc, violation) => {
            acc[violation.property] = violation.message;
            return acc;
          },
          {},
        );
      } else {
        toast.error('Failed to create collection');
      }
      return false;
    } finally {
      this._isSubmitting = false;
    }
  }
}

export function createCreateCollectionModalState(): CreateCollectionModalState {
  return new CreateCollectionModalState();
}
