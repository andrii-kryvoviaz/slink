<script lang="ts">
  import CollectionCreateFooter from '@slink/feature/Collection/CollectionList/CollectionCreateFooter.svelte';
  import CollectionEmptyState from '@slink/feature/Collection/CollectionList/CollectionEmptyState.svelte';
  import CollectionListItem from '@slink/feature/Collection/CollectionList/CollectionListItem.svelte';
  import CollectionSearchInput from '@slink/feature/Collection/CollectionList/CollectionSearchInput.svelte';
  import {
    type CollectionPickerVariant,
    collectionPickerContainerTheme,
    collectionPickerListTheme,
  } from '@slink/feature/Collection/CollectionPicker/CollectionPicker.theme';
  import { Loader } from '@slink/feature/Layout';

  import Icon from '@iconify/svelte';

  import type { CollectionResponse } from '@slink/api/Response';

  interface Props {
    collections: CollectionResponse[];
    selectedIds?: string[];
    isLoading?: boolean;
    togglingId?: string | null;
    disabled?: boolean;
    variant?: CollectionPickerVariant;
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
    showSearch: showSearchProp,
    onToggle,
    onCreateNew,
  }: Props = $props();

  let searchTerm = $state('');

  const showSearch = $derived(
    showSearchProp !== undefined ? showSearchProp : collections.length > 5,
  );

  const filteredCollections = $derived(
    searchTerm
      ? collections.filter((c) =>
          c.name.toLowerCase().includes(searchTerm.toLowerCase()),
        )
      : collections,
  );

  const isSelected = (collectionId: string) =>
    selectedIds.includes(collectionId);

  const isToggling = (collectionId: string) => togglingId === collectionId;
</script>

<div class={collectionPickerContainerTheme({ variant })}>
  {#if variant === 'popover' && showSearch}
    <CollectionSearchInput bind:value={searchTerm} />
  {/if}

  {#if isLoading}
    <div class="flex items-center justify-center py-10">
      <Loader variant="minimal" size="sm" />
    </div>
  {:else if collections.length === 0}
    <CollectionEmptyState {onCreateNew} />
  {:else}
    <div class="max-h-60 overflow-y-auto">
      <div class={collectionPickerListTheme({ variant })}>
        {#if filteredCollections.length === 0}
          <div class="flex flex-col items-center gap-2 py-6">
            <Icon
              icon="ph:magnifying-glass"
              class="w-5 h-5 text-gray-400 dark:text-gray-500"
            />
            <p class="text-sm text-gray-500 dark:text-gray-400">
              No matches found
            </p>
          </div>
        {:else}
          {#each filteredCollections as collection (collection.id)}
            <CollectionListItem
              {collection}
              selected={isSelected(collection.id)}
              isToggling={isToggling(collection.id)}
              {disabled}
              {variant}
              {onToggle}
            />
          {/each}
        {/if}
      </div>
    </div>

    {#if onCreateNew}
      <CollectionCreateFooter {onCreateNew} />
    {/if}
  {/if}
</div>
