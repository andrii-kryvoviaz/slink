import type { PickerCreate } from '@slink/ui/components/picker';

import type { Tag } from '@slink/api/Resources/TagResource';
import type { CollectionResponse } from '@slink/api/Response';

import {
  type CreateCollectionModalState,
  createCreateCollectionModalState,
} from '@slink/lib/state/CreateCollectionModalState.svelte';
import {
  type CreateTagModalState,
  createCreateTagModalState,
} from '@slink/lib/state/CreateTagModalState.svelte';
import {
  type PickerCatalog,
  createCollectionCatalog,
  createTagCatalog,
} from '@slink/lib/state/PickerCatalog.svelte';
import { firstViolationMessage } from '@slink/lib/utils/api/firstViolationMessage';
import { messages } from '@slink/lib/utils/i18n/messages/toast.language';
import { toast } from '@slink/lib/utils/ui/toast-sonner.svelte';

interface UploadPickerDeps<TItem extends { id: string }> {
  selected: () => TItem[];
  onChange: (items: TItem[]) => void;
  createFallbackMessage: string;
}

type CreatableModal<TItem> = {
  open(
    onSuccess?: (item: TItem) => void,
    onClose?: () => void,
    initialName?: string,
  ): void;
};

class UploadPickerState<
  TItem extends { id: string },
  TModal extends CreatableModal<TItem>,
> {
  open: boolean = $state(false);

  readonly catalog: PickerCatalog<TItem>;
  readonly modal: TModal;

  private _deps: UploadPickerDeps<TItem>;

  constructor(
    catalog: PickerCatalog<TItem>,
    modal: TModal,
    deps: UploadPickerDeps<TItem>,
  ) {
    this.catalog = catalog;
    this.modal = modal;
    this._deps = deps;
  }

  setOpen = (open: boolean) => {
    this.open = open;
    if (open) this.catalog.load();
  };

  toggle = (item: TItem) => {
    const selected = this._deps.selected();
    const isSelected = selected.some((i) => i.id === item.id);

    if (isSelected) {
      this._deps.onChange(selected.filter((i) => i.id !== item.id));
    } else {
      this._deps.onChange([...selected, item]);
    }
  };

  detach = (id: string) => {
    this._deps.onChange(this._deps.selected().filter((i) => i.id !== id));
  };

  private attach = (item: TItem) => {
    this._deps.onChange([...this._deps.selected(), item]);
  };

  private openDialog = (name?: string) => {
    this.open = false;
    this.modal.open(
      (item) => {
        this.catalog.addItem(item);
        this.attach(item);
      },
      () => {
        this.open = true;
      },
      name,
    );
  };

  readonly create: PickerCreate = {
    instant: async (name) => {
      try {
        this.attach(await this.catalog.create(name));
      } catch (error) {
        toast.error(
          firstViolationMessage(error, this._deps.createFallbackMessage),
        );
        throw error;
      }
    },
    detailed: (name) => this.openDialog(name),
  };
}

export const createUploadTagPicker = (deps: {
  selected: () => Tag[];
  onChange: (tags: Tag[]) => void;
}) =>
  new UploadPickerState<Tag, CreateTagModalState>(
    createTagCatalog(),
    createCreateTagModalState(),
    { ...deps, createFallbackMessage: messages.tag.failedToCreate },
  );

export const createUploadCollectionPicker = (deps: {
  selected: () => CollectionResponse[];
  onChange: (collections: CollectionResponse[]) => void;
}) =>
  new UploadPickerState<CollectionResponse, CreateCollectionModalState>(
    createCollectionCatalog(),
    createCreateCollectionModalState(),
    { ...deps, createFallbackMessage: messages.collection.failedToCreate },
  );
