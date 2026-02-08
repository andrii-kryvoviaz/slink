<script lang="ts">
  import {
    type AddToCollectionButtonSize,
    type AddToCollectionButtonVariant,
    addToCollectionButtonTheme,
    addToCollectionIconTheme,
  } from '@slink/feature/Collection/AddToCollectionButton/AddToCollectionButton.theme';
  import { CollectionPicker } from '@slink/feature/Collection';
  import { Tooltip, type TooltipVariant } from '@slink/ui/components/tooltip';
  import { Popover as PopoverPrimitive } from 'bits-ui';

  import { page } from '$app/state';
  import { toast } from '$lib/utils/ui/toast-sonner.svelte.js';
  import Icon from '@iconify/svelte';

  import { createCollectionPickerState } from '@slink/lib/state/CollectionPickerState.svelte';
  import { createCreateCollectionModalState } from '@slink/lib/state/CreateCollectionModalState.svelte';

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
  const createModalState = createCreateCollectionModalState();

  $effect(() => {
    picker.setImage(imageId, collectionIds);
  });

  let isOpen = $state(false);

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
      class="z-50 rounded-xl border border-gray-200/70 dark:border-gray-700/50 bg-white/95 dark:bg-gray-900/95 shadow-xl shadow-black/10 dark:shadow-black/30 overflow-hidden backdrop-blur-sm animate-in fade-in-0 zoom-in-95"
    >
      <CollectionPicker
        pickerState={picker}
        {createModalState}
        variant="popover"
        onToggle={(result) => {
          if (result) {
            onCollectionChange?.(result.collectionId, result.added);
          }
        }}
      />
    </PopoverPrimitive.Content>
  </PopoverPrimitive.Portal>
</PopoverPrimitive.Root>
