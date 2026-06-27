<script lang="ts">
  import {
    type PickerCreate,
    PickerItem,
    PickerList,
    type PickerVariant,
    type SelectionState,
    createSelectionResolver,
  } from '@slink/ui/components/picker';

  import Icon from '@iconify/svelte';

  import type { CollectionResponse } from '@slink/api/Response';

  interface Props {
    collections: CollectionResponse[];
    selectedIds?: string[];
    isLoading?: boolean;
    togglingId?: string | null;
    disabled?: boolean;
    variant?: PickerVariant;
    showSearch?: boolean;
    getItemState?: (id: string) => SelectionState;
    onToggle?: (collection: CollectionResponse) => void;
    create?: PickerCreate;
  }

  let {
    collections,
    selectedIds = [],
    isLoading = false,
    togglingId = null,
    disabled = false,
    variant = 'popover',
    getItemState,
    showSearch,
    onToggle,
    create,
  }: Props = $props();

  const resolveSelected = $derived(
    createSelectionResolver(selectedIds, getItemState),
  );
  const isToggling = (collectionId: string) => togglingId === collectionId;

  const filterCollection = (
    collection: CollectionResponse,
    searchTerm: string,
  ) => collection.name.toLowerCase().includes(searchTerm.toLowerCase());
</script>

<PickerList
  items={collections}
  {variant}
  color="indigo"
  {isLoading}
  {showSearch}
  searchPlaceholder="Search"
  filterFn={filterCollection}
  {create}
>
  {#snippet emptyIcon()}
    <Icon icon="ph:folder-simple-duotone" class="h-[18px] w-[18px]" />
  {/snippet}
  {#snippet emptyMessage()}No collections yet{/snippet}
  {#snippet emptyDescription()}Organize uploads into named groups{/snippet}
  {#snippet children({ item, highlighted })}
    {@const collection = item as CollectionResponse}
    <PickerItem
      selected={resolveSelected(collection.id)}
      isToggling={isToggling(collection.id)}
      {disabled}
      {variant}
      color="indigo"
      {highlighted}
      onclick={() => onToggle?.(collection)}
    >
      {#snippet children()}{collection.name}{/snippet}
    </PickerItem>
  {/snippet}
</PickerList>
