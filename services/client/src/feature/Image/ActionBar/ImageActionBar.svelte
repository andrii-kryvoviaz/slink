<script lang="ts">
  import {
    CollectionPicker,
    CreateCollectionDialog,
  } from '@slink/feature/Collection';
  import { ImageDeletePopover } from '@slink/feature/Image';
  import { Loader } from '@slink/feature/Layout';
  import { CreateTagDialog, TagPicker } from '@slink/feature/Tag';
  import { ButtonGroup, ButtonGroupItem } from '@slink/ui/components';
  import * as DropdownMenu from '@slink/ui/components/dropdown-menu/index.js';
  import { Overlay } from '@slink/ui/components/popover';
  import { TooltipProvider } from '@slink/ui/components/tooltip';

  import { page } from '$app/state';
  import Icon from '@iconify/svelte';
  import { cubicOut } from 'svelte/easing';
  import { scale } from 'svelte/transition';

  import type { Tag } from '@slink/api/Resources/TagResource';
  import type { CollectionReference } from '@slink/api/Response/Collection/CollectionResponse';

  import type { ShareFormat } from '@slink/lib/settings';

  import { cn } from '@slink/utils/ui';

  import ShareFormatMenu from '../ShareFormat/ShareFormatMenu.svelte';
  import type { ActionButton, ActionLayout } from './ImageActionBar.theme';
  import {
    actionButtonVariants,
    iconSizeVariants,
    shareCapsuleVariants,
  } from './ImageActionBar.theme';
  import { createImageActionsState } from './ImageActionsState.svelte';

  interface Props {
    image: {
      id: string;
      fileName: string;
      isPublic: boolean;
      collectionIds?: string[];
      tagIds?: string[];
    };
    buttons?: ActionButton[];
    compact?: boolean;
    layout?: ActionLayout;
    on?: {
      imageDelete?: (imageId: string) => void;
      collectionChange?: (
        imageId: string,
        collections: CollectionReference[],
      ) => void;
      tagChange?: (imageId: string, tags: Tag[]) => void;
    };
  }

  let {
    image = $bindable(),
    buttons = ['download', 'copy', 'collection', 'visibility', 'delete'],
    compact = false,
    layout = 'default',
    on,
  }: Props = $props();

  const isHero = $derived(layout === 'hero');
  const iconClass = $derived(iconSizeVariants({ layout }));
  const capsule = $derived(shareCapsuleVariants({ layout }));

  const { settings } = page.data;
  const selectedFormat = $derived(settings.share.format);

  const actions = createImageActionsState({
    getImage: () => image,
    onImageUpdate: (updated) => (image = updated),
    onImageDelete: (id) => on?.imageDelete?.(id),
    onCollectionChange: (id, ids) => on?.collectionChange?.(id, ids),
    onTagChange: (id, tags) => on?.tagChange?.(id, tags),
  });

  const visibleButtons = $derived(actions.filterVisibleButtons(buttons));
  const hasDownload = $derived(visibleButtons.includes('download'));
  const hasCopy = $derived(visibleButtons.includes('copy'));
  const hasShareCapsule = $derived(hasDownload || hasCopy);
  const hasDelete = $derived(visibleButtons.includes('delete'));
  const middleButtons = $derived(
    visibleButtons.filter(
      (button) =>
        button !== 'download' && button !== 'copy' && button !== 'delete',
    ),
  );
  const showCapsuleLabels = $derived(isHero || !compact);
  const copyDisabled = $derived(
    actions.shareIsLoading || actions.isCopied.active,
  );

  const copyTooltip = $derived.by(() => {
    if (actions.shareIsLoading) return 'Generating...';
    if (actions.isCopied.active) return 'Copied!';
    return 'Copy link';
  });

  const visibilityTooltip = $derived.by(() => {
    if (image.isPublic) return 'Make private';
    return 'Make public';
  });

  const handleCopyFormatSelect = (format: ShareFormat) => {
    settings.share = { format };
    actions.handleCopy(format);
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
    <Icon icon="tabler:link" class={iconClass} />
  {/if}
{/snippet}

{#snippet deletePopoverContent()}
  <ImageDeletePopover
    loading={actions.deleteIsLoading}
    close={() => (actions.popover.delete = false)}
    confirm={({ preserveOnDiskAfterDeletion }) =>
      actions.handleDelete(preserveOnDiskAfterDeletion)}
  />
{/snippet}

{#snippet downloadZone()}
  <ButtonGroupItem
    variant="primary"
    size="md"
    class={capsule.download()}
    onclick={actions.handleDownload}
    disabled={actions.downloadIsLoading}
    aria-label="Download image"
    tooltip={showCapsuleLabels ? undefined : 'Download'}
  >
    {@render loaderOrIcon(
      'lucide:download',
      actions.downloadIsLoading,
      capsule.downloadIcon(),
    )}
    {#if showCapsuleLabels}
      <span class={capsule.label()}>Download</span>
    {/if}
  </ButtonGroupItem>
{/snippet}

{#snippet copyZone()}
  <ButtonGroupItem
    variant="secondary"
    size="md"
    class={capsule.copy()}
    onclick={() => actions.handleCopy(selectedFormat)}
    disabled={copyDisabled}
    aria-label="Copy image link"
    tooltip={copyTooltip}
  >
    {@render copyIconContent()}
    {#if showCapsuleLabels}
      <span class={capsule.label()}>Copy</span>
    {/if}
  </ButtonGroupItem>
  <DropdownMenu.Root>
    <DropdownMenu.Trigger disabled={copyDisabled}>
      {#snippet child({ props })}
        <ButtonGroupItem
          {...props}
          variant="secondary"
          size="md"
          class={capsule.caret()}
          disabled={copyDisabled}
          aria-label="Copy link options"
        >
          <Icon icon="ph:caret-down" class="h-2.5 w-2.5" />
        </ButtonGroupItem>
      {/snippet}
    </DropdownMenu.Trigger>
    <ShareFormatMenu
      selected={selectedFormat}
      onSelect={handleCopyFormatSelect}
    />
  </DropdownMenu.Root>
{/snippet}

{#snippet shareCapsule()}
  <div class={capsule.capsule()}>
    {#if hasDownload}
      {@render downloadZone()}
    {/if}
    {#if hasCopy}
      {@render copyZone()}
    {/if}
  </div>
{/snippet}

{#snippet visibilityButton()}
  <ButtonGroupItem
    variant="default"
    size="md"
    class={actionButtonVariants({ layout })}
    onclick={actions.handleVisibilityChange}
    disabled={actions.visibilityIsLoading}
    aria-label={visibilityTooltip}
    aria-pressed={image.isPublic}
    tooltip={visibilityTooltip}
  >
    {@render loaderOrIcon(actions.visibilityIcon, actions.visibilityIsLoading)}
  </ButtonGroupItem>
{/snippet}

{#snippet deleteButton()}
  <Overlay
    bind:open={actions.popover.delete}
    variant="floating"
    contentProps={{ align: 'end' }}
  >
    {#snippet trigger()}
      <ButtonGroupItem
        variant="destructive"
        size="md"
        class={actionButtonVariants({ layout, variant: 'destructive' })}
        aria-label="Delete image"
        disabled={actions.deleteIsLoading}
        tooltip="Delete image"
        disableTooltip={actions.popover.delete}
      >
        <Icon icon="lucide:trash-2" class={iconClass} />
      </ButtonGroupItem>
    {/snippet}
    {@render deletePopoverContent()}
  </Overlay>
{/snippet}

{#snippet deleteSection()}
  {#if hasDelete}
    <div class="w-px h-[18px] bg-gray-200 dark:bg-gray-700 mx-0.5"></div>
    {@render deleteButton()}
  {/if}
{/snippet}

{#snippet collectionButton()}
  <Overlay
    bind:open={actions.popover.collection}
    variant="floating"
    size="none"
    contentProps={{ align: 'end' }}
  >
    {#snippet trigger()}
      <ButtonGroupItem
        variant="default"
        size="md"
        class={actionButtonVariants({ layout })}
        aria-label="Add to collection"
        tooltip="Add to collection"
        disableTooltip={actions.popover.collection}
      >
        <Icon icon="lucide:folder" class={iconClass} />
      </ButtonGroupItem>
    {/snippet}
    <CollectionPicker
      pickerState={actions.collectionPickerState}
      createModalState={actions.createCollectionModalState}
      variant="popover"
      onToggle={actions.handleCollectionToggle}
      onBeforeCreate={actions.popover.suspend}
      onAfterClose={actions.popover.restore}
    />
  </Overlay>
{/snippet}

{#snippet tagButton()}
  <Overlay
    bind:open={actions.popover.tag}
    variant="floating"
    size="none"
    contentProps={{ align: 'end' }}
  >
    {#snippet trigger()}
      <ButtonGroupItem
        variant="default"
        size="md"
        class={actionButtonVariants({ layout })}
        aria-label="Manage tags"
        tooltip="Manage tags"
        disableTooltip={actions.popover.tag}
      >
        <Icon icon="lucide:tag" class={iconClass} />
      </ButtonGroupItem>
    {/snippet}
    <TagPicker
      pickerState={actions.tagPickerState}
      createModalState={actions.createTagModalState}
      variant="popover"
      onToggle={actions.handleTagToggle}
      onBeforeCreate={actions.popover.suspend}
      onAfterClose={actions.popover.restore}
    />
  </Overlay>
{/snippet}

{#snippet renderButton(button: ActionButton)}
  {#if button === 'visibility'}
    {@render visibilityButton()}
  {:else if button === 'collection'}
    {@render collectionButton()}
  {:else if button === 'tag'}
    {@render tagButton()}
  {/if}
{/snippet}

<TooltipProvider delayDuration={300}>
  {#if isHero}
    <div
      class="flex items-center gap-3"
      role="toolbar"
      aria-label="Image actions"
    >
      {#if hasShareCapsule}
        {@render shareCapsule()}
      {/if}
      <div class="flex items-center gap-1">
        {#each middleButtons as button (button)}
          {@render renderButton(button)}
        {/each}
        {@render deleteSection()}
      </div>
    </div>
  {:else}
    <ButtonGroup
      variant="glass"
      size="md"
      gap="xs"
      padding="sm"
      class="rounded-full"
      role="toolbar"
      aria-label="Image actions"
    >
      {#if hasShareCapsule}
        {@render shareCapsule()}
      {/if}
      {#each middleButtons as button (button)}
        {@render renderButton(button)}
      {/each}
      {@render deleteSection()}
    </ButtonGroup>
  {/if}
</TooltipProvider>

<CreateCollectionDialog modalState={actions.createCollectionModalState} />
<CreateTagDialog modalState={actions.createTagModalState} />
