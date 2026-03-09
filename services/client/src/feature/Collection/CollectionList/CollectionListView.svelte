<script lang="ts">
  import {
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
    onCreateNew?: () => void;
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
    onCreateNew,
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
  searchPlaceholder="Search collections"
  filterFn={filterCollection}
  {onCreateNew}
>
  {#snippet emptyIcon()}
    <Icon
      icon="ph:folder-simple-duotone"
      class="w-5 h-5 text-gray-400 dark:text-gray-500"
    />
  {/snippet}
  {#snippet emptyMessage()}No collections yet{/snippet}
  {#snippet emptyAction()}
    {#if onCreateNew}Create your first collection{/if}
  {/snippet}
  {#snippet createFooter()}
    {#if onCreateNew}New collection{/if}
  {/snippet}
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
      {#snippet children()}{@html collection.name}{/snippet}
    </PickerItem>
  {/snippet}
</PickerList>
