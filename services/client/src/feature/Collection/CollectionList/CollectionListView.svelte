<script lang="ts">
  import {
    PickerItem,
    PickerList,
    type PickerVariant,
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
    showSearch,
    onToggle,
    onCreateNew,
  }: Props = $props();

  const isSelected = (collectionId: string) =>
    selectedIds.includes(collectionId);
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
    <Icon icon="ph:folder-simple-dashed" class="w-8 h-8" />
  {/snippet}
  {#snippet emptyMessage()}No collections yet{/snippet}
  {#snippet emptyAction()}
    {#if onCreateNew}Create your first collection{/if}
  {/snippet}
  {#snippet createFooter()}
    {#if onCreateNew}New collection{/if}
  {/snippet}
  {#snippet children({ item })}
    {@const collection = item as CollectionResponse}
    <PickerItem
      selected={isSelected(collection.id)}
      isToggling={isToggling(collection.id)}
      {disabled}
      {variant}
      color="indigo"
      onclick={() => onToggle?.(collection)}
    >
      {#snippet children()}{@html collection.name}{/snippet}
    </PickerItem>
  {/snippet}
</PickerList>
