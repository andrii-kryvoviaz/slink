import { ApiClient } from '@slink/api';

import { ReactiveState } from '@slink/api/ReactiveState';
import type { Tag } from '@slink/api/Resources/TagResource';

import { AbstractFormState } from '@slink/lib/state/core/AbstractFormState.svelte';

export class CreateTagModalState extends AbstractFormState<Tag> {
  private _submit = ReactiveState<Tag>(
    (data: { name: string; parentId?: string }) =>
      ApiClient.tag.create(data).then(({ id }) => ApiClient.tag.getById(id)),
  );

  open(onCreated?: (tag: Tag) => void) {
    super.open(onCreated);
  }

  async submit(data: { name: string; parentId?: string }): Promise<boolean> {
    return this.runSubmit(this._submit, data);
  }
}

export function createCreateTagModalState(): CreateTagModalState {
  return new CreateTagModalState();
}
