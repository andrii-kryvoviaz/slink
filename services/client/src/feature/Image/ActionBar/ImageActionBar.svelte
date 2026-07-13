<script lang="ts">
  import {
    CollectionPicker,
    CreateCollectionDialog,
  } from '@slink/feature/Collection';
  import { ImageDeletePopover } from '@slink/feature/Image';
  import { Loader } from '@slink/feature/Layout';
  import { CreateTagDialog, TagPicker } from '@slink/feature/Tag';
  import {
    ButtonGroup,
    ButtonGroupItem,
    DropdownSimple,
    DropdownSimpleGroup,
    DropdownSimpleItem,
  } from '@slink/ui/components';
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
    responsiveTierVariants,
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
    responsive?: boolean;
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
    responsive = false,
    layout = 'default',
    on,
  }: Props = $props();

  const isHero = $derived(layout === 'hero');
  const iconClass = $derived(iconSizeVariants({ layout }));
  const capsule = $derived(shareCapsuleVariants({ layout }));
  const tiers = responsiveTierVariants();

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

  const hasOverflowItems = $derived(middleButtons.length > 0 || hasDelete);

  let responsiveAnchor = $state<HTMLElement>();
  let compactTierActive = $state(false);

  const overlayContentProps = $derived.by(() => {
    if (responsive && compactTierActive) {
      return { align: 'end' as const, customAnchor: responsiveAnchor };
    }
    return { align: 'end' as const };
  });

  $effect(() => {
    if (!responsiveAnchor) return;

    const observer = new ResizeObserver(([entry]) => {
      compactTierActive = entry.contentRect.width > 0;
    });
    observer.observe(responsiveAnchor);

    return () => observer.disconnect();
  });

  const handleCopyFormatSelect = (format: ShareFormat) => {
    settings.share = { format };
    actions.handleCopy(format);
  };

  const openCollectionFromMenu = () => {
    actions.overlays.collection = true;
  };

  const openTagFromMenu = () => {
    actions.overlays.tag = true;
  };

  const openDeleteFromMenu = () => {
    actions.overlays.delete = true;
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
    close={() => (actions.overlays.delete = false)}
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

{#snippet copyZone(active: boolean)}
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
  {#if active}
    <DropdownMenu.Root bind:open={actions.overlays.copyFormats}>
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
  {/if}
{/snippet}

{#snippet shareCapsule(active: boolean)}
  <div class={capsule.capsule()}>
    {#if hasDownload}
      {@render downloadZone()}
    {/if}
    {#if hasCopy}
      {@render copyZone(active)}
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
    bind:open={actions.overlays.delete}
    variant="floating"
    contentProps={overlayContentProps}
  >
    {#snippet trigger()}
      <ButtonGroupItem
        variant="destructive"
        size="md"
        class={actionButtonVariants({ layout, variant: 'destructive' })}
        aria-label="Delete image"
        disabled={actions.deleteIsLoading}
        tooltip="Delete image"
        disableTooltip={actions.overlays.delete}
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
    bind:open={actions.overlays.collection}
    variant="floating"
    size="none"
    contentProps={overlayContentProps}
  >
    {#snippet trigger()}
      <ButtonGroupItem
        variant="default"
        size="md"
        class={actionButtonVariants({ layout })}
        aria-label="Add to collection"
        tooltip="Add to collection"
        disableTooltip={actions.overlays.collection}
      >
        <Icon icon="lucide:folder" class={iconClass} />
      </ButtonGroupItem>
    {/snippet}
    <CollectionPicker
      pickerState={actions.collectionPickerState}
      createModalState={actions.createCollectionModalState}
      variant="popover"
      onClose={() => (actions.overlays.collection = false)}
      onToggle={actions.handleCollectionToggle}
      onBeforeCreate={actions.overlays.suspend}
      onAfterClose={actions.overlays.restore}
    >
      {#snippet title()}Add to collection{/snippet}
    </CollectionPicker>
  </Overlay>
{/snippet}

{#snippet tagButton()}
  <Overlay
    bind:open={actions.overlays.tag}
    variant="floating"
    size="none"
    contentProps={overlayContentProps}
  >
    {#snippet trigger()}
      <ButtonGroupItem
        variant="default"
        size="md"
        class={actionButtonVariants({ layout })}
        aria-label="Manage tags"
        tooltip="Manage tags"
        disableTooltip={actions.overlays.tag}
      >
        <Icon icon="lucide:tag" class={iconClass} />
      </ButtonGroupItem>
    {/snippet}
    <TagPicker
      pickerState={actions.tagPickerState}
      createModalState={actions.createTagModalState}
      variant="popover"
      onClose={() => (actions.overlays.tag = false)}
      onToggle={actions.handleTagToggle}
      onBeforeCreate={actions.overlays.suspend}
      onAfterClose={actions.overlays.restore}
    >
      {#snippet title()}Manage tags{/snippet}
    </TagPicker>
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

{#snippet overflowMenuItem(button: ActionButton)}
  {#if button === 'visibility'}
    <DropdownSimpleItem on={{ click: actions.handleVisibilityChange }}>
      {#snippet icon()}
        <Icon icon={actions.visibilityIcon} class="h-4 w-4" />
      {/snippet}
      {#if image.isPublic}
        <span>Make private</span>
      {:else}
        <span>Make public</span>
      {/if}
    </DropdownSimpleItem>
  {:else if button === 'collection'}
    <DropdownSimpleItem on={{ click: openCollectionFromMenu }}>
      {#snippet icon()}
        <Icon icon="lucide:folder" class="h-4 w-4" />
      {/snippet}
      <span>Add to collection</span>
    </DropdownSimpleItem>
  {:else if button === 'tag'}
    <DropdownSimpleItem on={{ click: openTagFromMenu }}>
      {#snippet icon()}
        <Icon icon="lucide:tag" class="h-4 w-4" />
      {/snippet}
      <span>Manage tags</span>
    </DropdownSimpleItem>
  {/if}
{/snippet}

{#snippet overflowMenu()}
  <DropdownSimple bind:open={actions.overlays.overflow}>
    {#snippet trigger(triggerProps)}
      <ButtonGroupItem
        {...triggerProps}
        variant="default"
        size="md"
        class={actionButtonVariants({ layout })}
        aria-label="Image actions"
      >
        <Icon icon="lucide:ellipsis" class={iconClass} />
      </ButtonGroupItem>
    {/snippet}
    <DropdownSimpleGroup>
      {#each middleButtons as button (button)}
        {@render overflowMenuItem(button)}
      {/each}
      {#if hasDelete}
        <DropdownMenu.Separator />
        <DropdownSimpleItem danger={true} on={{ click: openDeleteFromMenu }}>
          {#snippet icon()}
            <Icon icon="lucide:trash-2" class="h-4 w-4" />
          {/snippet}
          <span>Delete image</span>
        </DropdownSimpleItem>
      {/if}
    </DropdownSimpleGroup>
  </DropdownSimple>
{/snippet}

{#snippet fullBar(active: boolean)}
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
      {@render shareCapsule(active)}
    {/if}
    {#each middleButtons as button (button)}
      {@render renderButton(button)}
    {/each}
    {@render deleteSection()}
  </ButtonGroup>
{/snippet}

{#snippet compactBar(active: boolean)}
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
      {@render shareCapsule(active)}
    {/if}
    {#if hasOverflowItems}
      {@render overflowMenu()}
    {/if}
  </ButtonGroup>
{/snippet}

<TooltipProvider delayDuration={300}>
  {#if isHero}
    <div
      class="flex items-center gap-3"
      role="toolbar"
      aria-label="Image actions"
    >
      {#if hasShareCapsule}
        {@render shareCapsule(true)}
      {/if}
      <div class="flex items-center gap-1">
        {#each middleButtons as button (button)}
          {@render renderButton(button)}
        {/each}
        {@render deleteSection()}
      </div>
    </div>
  {:else if responsive}
    <div>
      <div class={tiers.full()}>
        {@render fullBar(!compactTierActive)}
      </div>
      <div class={tiers.compact()} bind:this={responsiveAnchor}>
        {@render compactBar(compactTierActive)}
      </div>
    </div>
  {:else}
    {@render fullBar(true)}
  {/if}
</TooltipProvider>

<CreateCollectionDialog modalState={actions.createCollectionModalState} />
<CreateTagDialog modalState={actions.createTagModalState} />
