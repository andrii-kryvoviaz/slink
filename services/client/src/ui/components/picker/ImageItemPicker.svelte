<script lang="ts" generics="TItem extends { id: string }">
  import type { PickerVariant } from '@slink/ui/components/picker';
  import type { Snippet } from 'svelte';

  import type { ImagePickerState } from '@slink/lib/state/ImagePickerState.svelte';

  interface Props {
    pickerState: ImagePickerState<TItem>;
    createModalState: {
      open(onSuccess?: (item: TItem) => void, onClose?: () => void): void;
    };
    variant?: PickerVariant;
    onToggle?: (result: { added: boolean; itemId: string }) => void;
    onBeforeCreate?: () => void;
    onAfterClose?: () => void;
    listView: Snippet<
      [
        {
          items: TItem[];
          selectedIds: string[];
          isLoading: boolean;
          togglingId: string | null;
          variant: PickerVariant;
          onToggle: (item: TItem) => void;
          onCreateNew: () => void;
        },
      ]
    >;
  }

  let {
    pickerState,
    createModalState,
    variant = 'popover',
    onToggle,
    onBeforeCreate,
    onAfterClose,
    listView,
  }: Props = $props();

  const selectedIds = $derived(
    pickerState.items
      .filter((item) => pickerState.isAssigned(item.id))
      .map((item) => item.id),
  );

  const handleToggle = async (item: TItem) => {
    const result = await pickerState.toggle(item);
    if (result && onToggle) {
      onToggle(result);
    }
  };

  const handleCreateNew = () => {
    onBeforeCreate?.();
    createModalState.open((item) => {
      pickerState.addItem(item);
    }, onAfterClose);
  };
</script>

{@render listView({
  items: pickerState.items,
  selectedIds,
  isLoading: pickerState.isLoading,
  togglingId: pickerState.actionLoadingId,
  variant,
  onToggle: handleToggle,
  onCreateNew: handleCreateNew,
})}
