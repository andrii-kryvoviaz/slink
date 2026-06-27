import { ReactiveState } from '@slink/api/ReactiveState';
import type { CollectionResponse } from '@slink/api/Response';

import { createCollection } from '@slink/lib/state/PickerCatalog.svelte';
import { AbstractFormState } from '@slink/lib/state/core/AbstractFormState.svelte';

export class CreateCollectionModalState extends AbstractFormState<CollectionResponse> {
  private _initialName: string = $state('');

  private _submit = ReactiveState<CollectionResponse>(
    (data: { name: string; description?: string }) => createCollection(data),
  );

  get initialName() {
    return this._initialName;
  }

  open(
    onCreated?: (collection: CollectionResponse) => void,
    onClose?: () => void,
    initialName?: string,
  ) {
    this._initialName = initialName ?? '';
    super.open(onCreated, onClose);
  }

  async submit(data: { name: string; description?: string }): Promise<boolean> {
    return this.runSubmit(this._submit, data);
  }
}

export function createCreateCollectionModalState(): CreateCollectionModalState {
  return new CreateCollectionModalState();
}
