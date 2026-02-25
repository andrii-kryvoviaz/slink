import { ApiClient } from '@slink/api';

import type { CollectionResponse } from '@slink/api/Response';

import { AbstractFormState } from '@slink/lib/state/core/AbstractFormState.svelte';

export class CreateCollectionModalState extends AbstractFormState<CollectionResponse> {
  open(onCreated?: (collection: CollectionResponse) => void) {
    super.open(onCreated);
  }

  async submit(data: { name: string; description?: string }): Promise<boolean> {
    return this.handleSubmit(() => ApiClient.collection.create(data));
  }
}

export function createCreateCollectionModalState(): CreateCollectionModalState {
  return new CreateCollectionModalState();
}
