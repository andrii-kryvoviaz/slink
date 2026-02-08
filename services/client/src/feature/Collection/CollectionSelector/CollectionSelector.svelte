<script lang="ts">
  import {
    collectionSelectorContainerVariants,
    collectionSelectorIconContainerVariants,
    collectionSelectorIconVariants,
    collectionSelectorInputVariants,
    type CollectionSelectorContainerVariants,
  } from '@slink/feature/Collection/CollectionSelector/CollectionSelector.theme';
  import Badge from '@slink/feature/Text/Badge/Badge.svelte';
  import { Popover } from 'bits-ui';

  import Icon from '@iconify/svelte';

  import type { CollectionResponse } from '@slink/api/Response';

  import { cn } from '@slink/utils/ui';

  interface Props extends CollectionSelectorContainerVariants {
    collections: CollectionResponse[];
    selectedCollections?: CollectionResponse[];
    onCollectionsChange?: (collections: CollectionResponse[]) => void;
    placeholder?: string;
  }

  let {
    collections,
    selectedCollections = [],
    onCollectionsChange,
    disabled = false,
    placeholder = 'Search and select collections',
    variant = 'neon',
  }: Props = $props();

  let isOpen = $state(false);
  let searchTerm = $state('');
  let inputRef = $state<HTMLInputElement>();

  const selectedIds = $derived(new Set(selectedCollections.map((c) => c.id)));
  const hasSelected = $derived(selectedCollections.length > 0);
  const hasQuery = $derived(Boolean(searchTerm.trim()));

  const filteredCollections = $derived(
    collections.filter(
      (collection) =>
        !selectedIds.has(collection.id) &&
        collection.name.toLowerCase().includes(searchTerm.toLowerCase()),
    ),
  );

  const handleContainerClick = () => {
    if (disabled) return;
    inputRef?.focus();
    if (hasQuery) {
      isOpen = true;
    }
  };

  const handleContainerKeydown = (e: KeyboardEvent) => {
    if (disabled) return;
    if (e.key === 'Enter' || e.key === ' ') {
      e.preventDefault();
      inputRef?.focus();
      if (hasQuery) {
        isOpen = true;
      }
    }
  };

  const addCollection = (collection: CollectionResponse) => {
    if (disabled || selectedIds.has(collection.id)) return;
    onCollectionsChange?.([...selectedCollections, collection]);
    searchTerm = '';
    isOpen = false;
  };

  const removeCollection = (collectionId: string) => {
    if (disabled) return;
    onCollectionsChange?.(
      selectedCollections.filter((collection) => collection.id !== collectionId),
    );
  };

  $effect(() => {
    if (disabled) {
      isOpen = false;
      return;
    }

    if (hasQuery) {
      isOpen = true;
    } else {
      isOpen = false;
    }
  });
</script>

<div class="space-y-3">
  <div class="relative">
    <Popover.Root bind:open={isOpen}>
      <Popover.Trigger disabled={true}>
        {#snippet child({ props })}
          <div
            {...props}
            class={cn(
              collectionSelectorContainerVariants({
                variant,
                disabled,
              }),
            )}
            role="combobox"
            aria-expanded={!disabled && isOpen}
            aria-controls="collection-dropdown"
            aria-disabled={disabled}
            tabindex={disabled ? -1 : 0}
            onkeydown={handleContainerKeydown}
            onclick={handleContainerClick}
          >
            <div class="flex items-center gap-3 relative z-10">
              <div class="shrink-0">
                <div class={collectionSelectorIconContainerVariants({ variant })}>
                  <Icon
                    icon="ph:folder"
                    class={collectionSelectorIconVariants({ variant })}
                  />
                </div>
              </div>

              <div class="flex flex-wrap items-center gap-1.5 flex-1">
                {#each selectedCollections as collection (collection.id)}
                  <Badge variant="neon" size="sm" class="shrink-0">
                    <div class="flex items-center gap-1.5">
                      <Icon icon="ph:folder" class="h-3 w-3" />
                      <span class="font-medium truncate max-w-[10rem]">
                        {collection.name}
                      </span>
                      <button
                        type="button"
                        class="ml-1 rounded p-0.5 text-blue-600 hover:text-blue-700 hover:bg-blue-100/60 dark:text-blue-300 dark:hover:text-blue-200 dark:hover:bg-blue-500/20 transition-colors"
                        aria-label="Remove {collection.name} collection"
                        onclick={() => removeCollection(collection.id)}
                      >
                        <Icon icon="ph:x" class="h-2.5 w-2.5" />
                      </button>
                    </div>
                  </Badge>
                {/each}

                <div class="flex-1 min-w-30">
                  <input
                    bind:this={inputRef}
                    bind:value={searchTerm}
                    class={collectionSelectorInputVariants({ variant, disabled })}
                    placeholder={hasSelected
                      ? 'Add more collections...'
                      : placeholder}
                    {disabled}
                    autocomplete="off"
                    onfocus={() => {
                      if (!disabled && hasQuery) {
                        isOpen = true;
                      }
                    }}
                  />
                </div>
              </div>
            </div>
          </div>
        {/snippet}
      </Popover.Trigger>

      <Popover.Portal>
        <Popover.Content
          class="z-50 w-[var(--bits-popover-anchor-width)] bg-white dark:bg-gray-900/95 backdrop-blur-md border border-gray-200/60 dark:border-white/10 rounded-lg shadow-2xl shadow-gray-500/5 dark:shadow-black/50 ring-1 ring-gray-100/20 dark:ring-white/5 p-1"
          sideOffset={6}
          align="start"
          trapFocus={false}
          interactOutsideBehavior="close"
          onOpenAutoFocus={(e) => e.preventDefault()}
          onInteractOutside={() => (isOpen = false)}
          onFocusOutside={() => (isOpen = false)}
          onEscapeKeydown={() => (isOpen = false)}
        >
          <div class="max-h-60 overflow-y-auto py-1">
            {#if filteredCollections.length === 0}
              <div class="px-4 py-6 text-center text-sm text-gray-500 dark:text-white/50">
                No collections found
              </div>
            {:else}
              {#each filteredCollections as collection (collection.id)}
                <button
                  type="button"
                  class="w-full flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-blue-50/60 dark:hover:bg-blue-500/10 transition-colors text-left"
                  onclick={() => addCollection(collection)}
                >
                  <Icon icon="ph:folder" class="h-4 w-4 text-blue-500" />
                  <span class="truncate">{collection.name}</span>
                </button>
              {/each}
            {/if}
          </div>
        </Popover.Content>
      </Popover.Portal>
    </Popover.Root>
  </div>
</div>
