import { ApiClient } from '@slink/api';

import type { Tag } from '@slink/api/Resources/TagResource';

import { AbstractFormState } from '@slink/lib/state/core/AbstractFormState.svelte';

export class CreateTagModalState extends AbstractFormState<Tag> {
  open(onCreated?: (tag: Tag) => void) {
    super.open(onCreated);
  }

  async submit(data: { name: string; parentId?: string }): Promise<boolean> {
    return this.handleSubmit(async () => {
      const { id } = await ApiClient.tag.create(data);
      return ApiClient.tag.getById(id);
    });
  }
}

export function createCreateTagModalState(): CreateTagModalState {
  return new CreateTagModalState();
}
