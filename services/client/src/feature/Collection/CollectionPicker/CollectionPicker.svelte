<script lang="ts">
  import { CreateCollectionDialog } from '@slink/feature/Collection';
  import { Loader } from '@slink/feature/Layout';

  import Icon from '@iconify/svelte';

  import type { CollectionResponse } from '@slink/api/Response';

  import type { CollectionPickerState } from '@slink/lib/state/CollectionPickerState.svelte';
  import type { CreateCollectionModalState } from '@slink/lib/state/CreateCollectionModalState.svelte';

  import {
    type CollectionPickerVariant,
    collectionPickerCheckIconTheme,
    collectionPickerCheckboxTheme,
    collectionPickerContainerTheme,
    collectionPickerEmptyTheme,
    collectionPickerItemTheme,
    collectionPickerListTheme,
    collectionPickerNameTheme,
  } from './CollectionPicker.theme';

  interface Props {
    pickerState: CollectionPickerState;
    createModalState: CreateCollectionModalState;
    variant?: CollectionPickerVariant;
    onToggle?: (result: { added: boolean; collectionId: string }) => void;
  }

  let {
    pickerState,
    createModalState,
    variant = 'popover',
    onToggle,
  }: Props = $props();

  let searchTerm = $state('');
  let inputFocused = $state(false);

  const filteredCollections = $derived(
    searchTerm
      ? pickerState.collections.filter((c) =>
          c.name.toLowerCase().includes(searchTerm.toLowerCase()),
        )
      : pickerState.collections,
  );

  const showSearch = $derived(pickerState.collections.length > 5);

  const handleToggle = async (collection: CollectionResponse) => {
    const result = await pickerState.toggle(collection);
    if (result && onToggle) {
      onToggle(result);
    }
  };

  const handleCreateNew = () => {
    createModalState.open((collection) => {
      pickerState.addCollection(collection);
    });
  };
</script>

<div class={collectionPickerContainerTheme({ variant })}>
  {#if variant === 'popover' && showSearch}
    <div class="px-2 pt-2 pb-1 shrink-0">
      <div class="relative">
        <Icon
          icon="ph:magnifying-glass"
          class="absolute left-2.5 top-1/2 -translate-y-1/2 w-4 h-4 transition-colors {inputFocused
            ? 'text-gray-500 dark:text-gray-400'
            : 'text-gray-400 dark:text-gray-500'}"
        />
        <input
          bind:value={searchTerm}
          onfocus={() => (inputFocused = true)}
          onblur={() => (inputFocused = false)}
          placeholder="Search collections"
          class="w-full pl-9 pr-3 py-2 text-sm bg-transparent border-0 border-b border-gray-200 dark:border-gray-700 focus:border-gray-300 dark:focus:border-gray-600 outline-none placeholder:text-gray-400 dark:placeholder:text-gray-500 text-gray-900 dark:text-gray-100 transition-colors"
        />
      </div>
    </div>
  {/if}

  {#if pickerState.isLoading}
    <div class="flex items-center justify-center py-10">
      <Loader variant="minimal" size="sm" />
    </div>
  {:else if pickerState.isEmpty}
    <div class={collectionPickerEmptyTheme({ variant })}>
      <div class="flex flex-col items-center gap-3 py-6">
        <div
          class="w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center"
        >
          <Icon
            icon="ph:folder-simple-dashed"
            class="w-5 h-5 text-gray-400 dark:text-gray-500"
          />
        </div>
        <p class="text-sm text-gray-500 dark:text-gray-400">
          No collections yet
        </p>
        <button
          type="button"
          onclick={handleCreateNew}
          class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors"
        >
          Create your first collection
        </button>
      </div>
    </div>
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
        {/if}

        {#each filteredCollections as collection (collection.id)}
          {@const selected = pickerState.isInCollection(collection.id)}
          {@const isToggling = pickerState.isToggling(collection.id)}
          <button
            type="button"
            class={collectionPickerItemTheme({ variant, selected })}
            onclick={() => handleToggle(collection)}
            disabled={!!pickerState.actionLoadingId}
          >
            <span class={collectionPickerCheckboxTheme({ variant, selected })}>
              {#if isToggling}
                <Loader variant="minimal" size="xs" />
              {:else if selected}
                <Icon
                  icon="ph:check-bold"
                  class={collectionPickerCheckIconTheme({ variant })}
                />
              {/if}
            </span>
            <span class="flex-1 min-w-0">
              <span class={collectionPickerNameTheme({ variant, selected })}>
                {collection.name}
              </span>
            </span>
          </button>
        {/each}
      </div>
    </div>

    <div
      class="px-2 py-1.5 border-t border-gray-100 dark:border-gray-800/50 shrink-0"
    >
      <button
        type="button"
        onclick={handleCreateNew}
        class="flex items-center gap-2 px-2 py-1.5 rounded-md text-[13px] text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-50 dark:hover:bg-white/5 transition-colors w-full"
      >
        <Icon icon="ph:plus" class="w-3.5 h-3.5" />
        <span>New collection</span>
      </button>
    </div>
  {/if}
</div>

<CreateCollectionDialog modalState={createModalState} />
