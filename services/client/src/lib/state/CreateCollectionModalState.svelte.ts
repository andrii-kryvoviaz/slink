import { ApiClient } from '@slink/api';

import { ReactiveState } from '@slink/api/ReactiveState';
import type { CollectionResponse } from '@slink/api/Response';

import { AbstractFormState } from '@slink/lib/state/core/AbstractFormState.svelte';

export class CreateCollectionModalState extends AbstractFormState<CollectionResponse> {
  private _submit = ReactiveState<CollectionResponse>(
    (data: { name: string; description?: string }) =>
      ApiClient.collection.create(data),
  );

  open(
    onCreated?: (collection: CollectionResponse) => void,
    onClose?: () => void,
  ) {
    super.open(onCreated, onClose);
  }

  async submit(data: { name: string; description?: string }): Promise<boolean> {
    return this.runSubmit(this._submit, data);
  }
}

export function createCreateCollectionModalState(): CreateCollectionModalState {
  return new CreateCollectionModalState();
}
