<script lang="ts">
  import {
    type AddToCollectionButtonSize,
    type AddToCollectionButtonVariant,
    addToCollectionButtonTheme,
    addToCollectionIconTheme,
    collectionItemTheme,
    collectionListTheme,
  } from '@slink/feature/Collection/AddToCollectionButton/AddToCollectionButton.theme';
  import { Loader } from '@slink/feature/Layout';
  import { Tooltip, type TooltipVariant } from '@slink/ui/components/tooltip';
  import { Popover as PopoverPrimitive } from 'bits-ui';

  import { page } from '$app/state';
  import { toast } from '$lib/utils/ui/toast-sonner.svelte.js';
  import Icon from '@iconify/svelte';

  import type { CollectionResponse } from '@slink/api/Response';

  import { createCollectionPickerState } from '@slink/lib/state/CollectionPickerState.svelte';

  interface Props {
    imageId: string;
    imageOwnerId: string;
    collectionIds?: string[];
    size?: AddToCollectionButtonSize;
    variant?: AddToCollectionButtonVariant;
    tooltipVariant?: TooltipVariant;
    onCollectionChange?: (collectionId: string, added: boolean) => void;
  }

  let {
    imageId,
    imageOwnerId,
    collectionIds = [],
    size = 'md',
    variant = 'default',
    tooltipVariant = 'subtle',
    onCollectionChange,
  }: Props = $props();

  const currentUser = $derived(page.data.user ?? null);
  const isOwnImage = $derived(currentUser?.id === imageOwnerId);
  const isAuthenticated = $derived(!!currentUser);

  const picker = createCollectionPickerState();

  $effect(() => {
    picker.setImage(imageId, collectionIds);
  });

  let isOpen = $state(false);

  async function handleToggle(collection: CollectionResponse) {
    const result = await picker.toggle(collection);
    if (result) {
      onCollectionChange?.(result.collectionId, result.added);
    }
  }

  function handleOpenChange(open: boolean) {
    isOpen = open;
    if (open && !picker.isLoaded) {
      picker.load();
    }
  }

  function handleButtonClick(e: MouseEvent) {
    e.stopPropagation();
    e.preventDefault();

    if (!isAuthenticated) {
      toast.info('Sign in to add images to collections');
      return;
    }

    if (!isOwnImage) {
      toast.info('You can only add your own images to collections');
      return;
    }
  }

  const hasCollections = $derived(
    picker.collections.some((c) => picker.isInCollection(c.id)),
  );
  const tooltipText = $derived(
    !isAuthenticated
      ? 'Sign in to add to collection'
      : !isOwnImage
        ? 'Only own images can be added'
        : 'Add to collection',
  );
</script>

<PopoverPrimitive.Root bind:open={isOpen} onOpenChange={handleOpenChange}>
  <Tooltip side="top" sideOffset={6} variant={tooltipVariant}>
    {#snippet trigger()}
      <PopoverPrimitive.Trigger>
        {#snippet child({ props })}
          <button
            {...props}
            class={addToCollectionButtonTheme({
              size,
              variant,
              active: hasCollections,
              loading: picker.isLoading,
            })}
            onclick={!isAuthenticated || !isOwnImage
              ? handleButtonClick
              : undefined}
            aria-label={tooltipText}
          >
            <span class="relative flex items-center justify-center">
              {#if hasCollections}
                <Icon
                  icon="ph:folder-simple-plus-fill"
                  class={addToCollectionIconTheme({
                    size,
                    variant,
                    active: true,
                    loading: picker.isLoading,
                  })}
                />
              {:else}
                <Icon
                  icon="ph:folder-simple-plus"
                  class={addToCollectionIconTheme({
                    size,
                    variant,
                    active: false,
                    loading: picker.isLoading,
                  })}
                />
              {/if}
            </span>
          </button>
        {/snippet}
      </PopoverPrimitive.Trigger>
    {/snippet}
    {tooltipText}
  </Tooltip>

  <PopoverPrimitive.Portal>
    <PopoverPrimitive.Content
      sideOffset={8}
      align="end"
      class="z-50 w-64 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-lg animate-in fade-in-0 zoom-in-95"
    >
      <div class="px-3 py-2 border-b border-gray-100 dark:border-gray-800">
        <h4 class="text-sm font-medium text-gray-900 dark:text-white">
          Add to Collection
        </h4>
      </div>

      {#if picker.isLoading}
        <div class="flex items-center justify-center py-8">
          <Loader variant="minimal" size="sm" />
        </div>
      {:else if picker.isEmpty}
        <div class="px-3 py-6 text-center">
          <Icon
            icon="ph:folder-simple-dashed"
            class="w-8 h-8 mx-auto text-gray-400 dark:text-gray-500 mb-2"
          />
          <p class="text-sm text-gray-500 dark:text-gray-400">
            No collections yet
          </p>
          <a
            href="/collections"
            class="mt-2 inline-block text-sm text-purple-600 dark:text-purple-400 hover:underline"
            onclick={() => (isOpen = false)}
          >
            Create one
          </a>
        </div>
      {:else}
        <div class={collectionListTheme()}>
          {#each picker.collections as collection (collection.id)}
            {@const isInCollection = picker.isInCollection(collection.id)}
            {@const isToggling = picker.isToggling(collection.id)}
            <button
              type="button"
              class={collectionItemTheme({ selected: isInCollection })}
              onclick={() => handleToggle(collection)}
              disabled={!!picker.actionLoadingId}
            >
              {#if isToggling}
                <Loader variant="minimal" size="xs" />
              {:else if isInCollection}
                <Icon
                  icon="ph:check-circle-fill"
                  class="w-4 h-4 text-purple-500 shrink-0"
                />
              {:else}
                <Icon
                  icon="ph:folder-simple"
                  class="w-4 h-4 text-gray-400 shrink-0"
                />
              {/if}
              <span class="truncate flex-1">{collection.name}</span>
              {#if collection.itemCount !== undefined}
                <span class="text-xs text-gray-400 dark:text-gray-500 shrink-0">
                  {collection.itemCount}
                </span>
              {/if}
            </button>
          {/each}
        </div>
      {/if}

      <div class="px-3 py-2 border-t border-gray-100 dark:border-gray-800">
        <a
          href="/collections"
          class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 hover:text-purple-600 dark:hover:text-purple-400 transition-colors"
          onclick={() => (isOpen = false)}
        >
          <Icon icon="ph:plus" class="w-4 h-4" />
          <span>Manage collections</span>
        </a>
      </div>
    </PopoverPrimitive.Content>
  </PopoverPrimitive.Portal>
</PopoverPrimitive.Root>
