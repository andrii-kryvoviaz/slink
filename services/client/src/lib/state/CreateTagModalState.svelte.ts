import { ReactiveState } from '@slink/api/ReactiveState';
import type { Tag } from '@slink/api/Resources/TagResource';

import { createTag } from '@slink/lib/state/PickerCatalog.svelte';
import { AbstractFormState } from '@slink/lib/state/core/AbstractFormState.svelte';

export class CreateTagModalState extends AbstractFormState<Tag> {
  private _initialName: string = $state('');

  private _submit = ReactiveState<Tag>(
    (data: { name: string; parentId?: string }) =>
      createTag(data.name, data.parentId),
  );

  get initialName() {
    return this._initialName;
  }

  open(
    onCreated?: (tag: Tag) => void,
    onClose?: () => void,
    initialName?: string,
  ) {
    this._initialName = initialName ?? '';
    super.open(onCreated, onClose);
  }

  async submit(data: { name: string; parentId?: string }): Promise<boolean> {
    return this.runSubmit(this._submit, data);
  }
}

export function createCreateTagModalState(): CreateTagModalState {
  return new CreateTagModalState();
}
