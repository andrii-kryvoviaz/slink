<script lang="ts">
  import { CollectionPicker } from '@slink/feature/Collection';
  import { ImageDeletePopover } from '@slink/feature/Image';
  import { Loader } from '@slink/feature/Layout';
  import { ButtonGroup, ButtonGroupItem } from '@slink/ui/components';
  import { Overlay } from '@slink/ui/components/popover';
  import { TooltipProvider } from '@slink/ui/components/tooltip';

  import Icon from '@iconify/svelte';
  import { cubicOut } from 'svelte/easing';
  import { scale } from 'svelte/transition';

  import { cn } from '@slink/utils/ui';

  import type { ActionButton, ActionLayout } from './ImageActionBar.theme';
  import {
    actionBarContainerVariants,
    actionBarSecondaryGroupVariants,
    actionButtonVariants,
    downloadIconVariants,
    downloadLabelVariants,
    iconSizeVariants,
  } from './ImageActionBar.theme';
  import { useImageActions } from './useImageActions.svelte';

  type ButtonPosition = 'first' | 'middle' | 'last' | 'only';

  interface Props {
    image: {
      id: string;
      fileName: string;
      isPublic: boolean;
      collectionIds?: string[];
    };
    buttons?: ActionButton[];
    compact?: boolean;
    layout?: ActionLayout;
    on?: {
      imageDelete?: (imageId: string) => void;
      collectionChange?: (imageId: string, collectionIds: string[]) => void;
    };
  }

  let {
    image = $bindable(),
    buttons = ['download', 'collection', 'copy', 'visibility', 'delete'],
    compact = false,
    layout = 'default',
    on,
  }: Props = $props();

  const isHero = $derived(layout === 'hero');
  const iconClass = $derived(iconSizeVariants({ layout }));

  const actions = useImageActions({
    getImage: () => image,
    onImageUpdate: (updated) => (image = updated),
    onImageDelete: (id) => on?.imageDelete?.(id),
    onCollectionChange: (id, ids) => on?.collectionChange?.(id, ids),
  });

  const visibleButtons = $derived(actions.filterVisibleButtons(buttons));

  const getPosition = (index: number, total: number): ButtonPosition => {
    if (total === 1) return 'only';
    if (index === 0) return 'first';
    if (index === total - 1) return 'last';
    return 'middle';
  };
</script>

{#snippet loaderOrIcon(icon: string, isLoading: boolean, extraClass?: string)}
  {#if isLoading}
    <div class={cn(iconClass, 'flex items-center justify-center')}>
      <Loader variant="minimal" size="xs" />
    </div>
  {:else}
    <Icon {icon} class={cn(iconClass, extraClass)} />
  {/if}
{/snippet}

{#snippet copyIconContent()}
  {#if actions.shareIsLoading}
    <div class={cn(iconClass, 'flex items-center justify-center')}>
      <Loader variant="minimal" size="xs" />
    </div>
  {:else if actions.isCopied.active}
    <div in:scale={{ duration: 300, easing: cubicOut }}>
      <Icon
        icon="lucide:check"
        class={cn(iconClass, 'text-green-600 dark:text-green-400')}
      />
    </div>
  {:else}
    <Icon icon="lucide:link" class={iconClass} />
  {/if}
{/snippet}

{#snippet deletePopoverContent()}
  <ImageDeletePopover
    loading={actions.deleteIsLoading}
    close={() => (actions.deletePopoverOpen = false)}
    confirm={({ preserveOnDiskAfterDeletion }) =>
      actions.handleDelete(preserveOnDiskAfterDeletion)}
  />
{/snippet}

{#snippet downloadButton(position: ButtonPosition)}
  <ButtonGroupItem
    variant="primary"
    size="md"
    position={isHero ? 'only' : position}
    class={actionButtonVariants({
      layout,
      variant: compact ? 'default' : 'primary',
    })}
    onclick={actions.handleDownload}
    disabled={actions.downloadIsLoading}
    aria-label="Download image"
    tooltip={compact && !isHero ? 'Download' : undefined}
  >
    {@render loaderOrIcon(
      'lucide:download',
      actions.downloadIsLoading,
      downloadIconVariants({ layout }),
    )}
    {#if isHero || !compact}
      <span class={downloadLabelVariants({ layout })}>Download</span>
    {/if}
  </ButtonGroupItem>
{/snippet}

{#snippet visibilityButton(position: ButtonPosition)}
  <ButtonGroupItem
    variant="default"
    size="md"
    position={isHero ? 'only' : position}
    class={actionButtonVariants({ layout })}
    onclick={actions.handleVisibilityChange}
    disabled={actions.visibilityIsLoading}
    aria-label={actions.visibilityTooltip}
    aria-pressed={image.isPublic}
    tooltip={actions.visibilityTooltip}
  >
    {@render loaderOrIcon(actions.visibilityIcon, actions.visibilityIsLoading)}
  </ButtonGroupItem>
{/snippet}

{#snippet copyButton(position: ButtonPosition)}
  <ButtonGroupItem
    variant={isHero ? 'default' : 'secondary'}
    size="md"
    position={isHero ? 'only' : position}
    class={actionButtonVariants({ layout, variant: 'secondary' })}
    onclick={actions.handleCopy}
    disabled={actions.shareIsLoading || actions.isCopied.active}
    aria-label="Copy image URL"
    tooltip={actions.copyTooltip}
  >
    {@render copyIconContent()}
  </ButtonGroupItem>
{/snippet}

{#snippet deleteButton(position: ButtonPosition)}
  <Overlay
    bind:open={actions.deletePopoverOpen}
    variant="floating"
    contentProps={{ align: 'end' }}
    triggerClass={isHero ? '' : 'flex-1'}
  >
    {#snippet trigger()}
      <ButtonGroupItem
        variant="destructive"
        size="md"
        position={isHero ? 'only' : position}
        class={actionButtonVariants({ layout, variant: 'destructive' })}
        aria-label="Delete image"
        disabled={actions.deleteIsLoading}
        tooltip="Delete image"
      >
        <Icon icon="lucide:trash-2" class={iconClass} />
      </ButtonGroupItem>
    {/snippet}
    {@render deletePopoverContent()}
  </Overlay>
{/snippet}

{#snippet collectionButton(position: ButtonPosition)}
  <Overlay
    bind:open={actions.collectionPopoverOpen}
    variant="floating"
    size="none"
    contentProps={{ align: 'end' }}
    triggerClass={isHero ? '' : 'flex-1'}
  >
    {#snippet trigger()}
      <ButtonGroupItem
        variant="default"
        size="md"
        position={isHero ? 'only' : position}
        class={actionButtonVariants({ layout })}
        aria-label="Add to collection"
        tooltip="Add to collection"
      >
        <Icon icon="lucide:folder" class={iconClass} />
      </ButtonGroupItem>
    {/snippet}
    <CollectionPicker
      pickerState={actions.collectionPickerState}
      createModalState={actions.createCollectionModalState}
      variant="popover"
      onToggle={({ added, collectionId }) => {
        const ids = image.collectionIds ?? [];
        const newIds = added
          ? [...ids, collectionId]
          : ids.filter((id) => id !== collectionId);
        image = { ...image, collectionIds: newIds };
        on?.collectionChange?.(image.id, newIds);
      }}
    />
  </Overlay>
{/snippet}

{#snippet renderButton(button: ActionButton, position: ButtonPosition)}
  {#if button === 'download'}
    {@render downloadButton(position)}
  {:else if button === 'visibility'}
    {@render visibilityButton(position)}
  {:else if button === 'copy'}
    {@render copyButton(position)}
  {:else if button === 'delete'}
    {@render deleteButton(position)}
  {:else if button === 'collection'}
    {@render collectionButton(position)}
  {/if}
{/snippet}

<TooltipProvider delayDuration={300}>
  {#if isHero}
    <div
      class={actionBarContainerVariants({ layout })}
      role="toolbar"
      aria-label="Image actions"
    >
      {#each visibleButtons as button, i (button)}
        {@const position = getPosition(i, visibleButtons.length)}
        {#if button === 'download'}
          {@render renderButton(button, position)}
        {/if}
      {/each}
      <div class={actionBarSecondaryGroupVariants({ layout })}>
        {#each visibleButtons as button}
          {#if button !== 'download' && button !== 'delete'}
            {@render renderButton(button, 'only')}
          {/if}
        {/each}
        <div class="w-px h-5 bg-gray-200 dark:bg-gray-700 mx-1"></div>
        {#if visibleButtons.includes('delete')}
          {@render renderButton('delete', 'only')}
        {/if}
      </div>
    </div>
  {:else}
    <ButtonGroup
      variant="glass"
      rounded="lg"
      size="md"
      gap="none"
      padding="none"
      role="toolbar"
      aria-label="Image actions"
    >
      {#each visibleButtons as button, i (button)}
        {@const position = getPosition(i, visibleButtons.length)}
        {@render renderButton(button, position)}
      {/each}
    </ButtonGroup>
  {/if}
</TooltipProvider>
