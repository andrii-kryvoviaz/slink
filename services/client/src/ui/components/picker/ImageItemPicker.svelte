<script lang="ts" generics="TItem extends { id: string }">
  import XIcon from '@lucide/svelte/icons/x';
  import type { PickerVariant } from '@slink/ui/components/picker';
  import type { Snippet } from 'svelte';

  import type { ImagePickerState } from '@slink/lib/state/ImagePickerState.svelte';

  import {
    pickerHeaderCloseTheme,
    pickerHeaderTheme,
    pickerHeaderTitleTheme,
  } from './picker.theme';

  interface Props {
    pickerState: ImagePickerState<TItem>;
    createModalState: {
      open(onSuccess?: (item: TItem) => void, onClose?: () => void): void;
    };
    variant?: PickerVariant;
    title?: Snippet;
    onClose?: () => void;
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
          onSearch: (term: string) => void;
        },
      ]
    >;
  }

  let {
    pickerState,
    createModalState,
    variant = 'popover',
    title,
    onClose,
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

{#if title || onClose}
  <div class={pickerHeaderTheme()}>
    {#if title}
      <span class={pickerHeaderTitleTheme()}>{@render title()}</span>
    {/if}
    {#if onClose}
      <button
        type="button"
        class={pickerHeaderCloseTheme()}
        aria-label="Close"
        onclick={onClose}
      >
        <XIcon class="h-4 w-4" />
      </button>
    {/if}
  </div>
{/if}

{@render listView({
  items: pickerState.items,
  selectedIds,
  isLoading: pickerState.isLoading,
  togglingId: pickerState.actionLoadingId,
  variant,
  onToggle: handleToggle,
  onCreateNew: handleCreateNew,
  onSearch: (term) => pickerState.search(term),
})}
