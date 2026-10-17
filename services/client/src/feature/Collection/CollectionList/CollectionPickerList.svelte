<script lang="ts">
  import {
    type PickerCreate,
    PickerItem,
    PickerList,
    type PickerVariant,
    type SelectionState,
    createSelectionResolver,
  } from '@slink/ui/components/picker';

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
    onSearch: (term: string) => void;
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
    onSearch,
  }: Props = $props();

  const resolveSelected = $derived(
    createSelectionResolver(selectedIds, getItemState),
  );
  const isToggling = (collectionId: string) => togglingId === collectionId;
</script>

<PickerList
  items={collections}
  {variant}
  color="indigo"
  {isLoading}
  {showSearch}
  searchPlaceholder="Search"
  {create}
  {onSearch}
>
  {#snippet emptyCaption()}Your collections will appear here{/snippet}
  {#snippet createFooter()}Create collection{/snippet}
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
