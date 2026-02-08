<script lang="ts">
  import { CollectionPicker } from '@slink/feature/Collection';
  import { ImageDeletePopover } from '@slink/feature/Image';
  import { Loader } from '@slink/feature/Layout';
  import { ButtonGroup, ButtonGroupItem } from '@slink/ui/components';
  import { Overlay } from '@slink/ui/components/popover';
  import { TooltipProvider } from '@slink/ui/components/tooltip';

  import { goto } from '$app/navigation';
  import { useGlobalSettings } from '$lib/state/GlobalSettings.svelte.js';
  import { useUploadHistoryFeed } from '$lib/state/UploadHistoryFeed.svelte.js';
  import { downloadByLink } from '$lib/utils/http/downloadByLink';
  import { useAutoReset } from '$lib/utils/time/useAutoReset.svelte';
  import { toast } from '$lib/utils/ui/toast-sonner.svelte.js';
  import { routes } from '$lib/utils/url/routes';
  import Icon from '@iconify/svelte';
  import { cubicOut } from 'svelte/easing';
  import { scale } from 'svelte/transition';

  import { ApiClient } from '@slink/api/Client';
  import { ReactiveState } from '@slink/api/ReactiveState';
  import type { ShareResponse } from '@slink/api/Response';

  import { createCollectionPickerState } from '@slink/lib/state/CollectionPickerState.svelte';
  import { createCreateCollectionModalState } from '@slink/lib/state/CreateCollectionModalState.svelte';

  import { cn } from '@slink/utils/ui';

  import {
    actionBarContainerVariants,
    actionBarSecondaryGroupVariants,
    heroButtonOverrides,
    iconSizeVariants,
  } from './ImageActionBar.theme';

  type ActionButton =
    | 'download'
    | 'visibility'
    | 'delete'
    | 'copy'
    | 'collection';
  type ActionLayout = 'default' | 'hero';
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
  const historyFeedState = useUploadHistoryFeed();
  const globalSettingsManager = useGlobalSettings();

  const allowOnlyPublicImages = $derived(
    globalSettingsManager.settings?.image?.allowOnlyPublicImages || false,
  );

  const visibleButtons = $derived(
    buttons.filter((button) => {
      if (button === 'visibility' && allowOnlyPublicImages) return false;
      return true;
    }),
  );

  const directLink = $derived(
    routes.image.view(image.fileName, undefined, { absolute: true }),
  );
  const iconClass = $derived(iconSizeVariants({ layout }));
  const visibilityTooltip = $derived(
    image.isPublic ? 'Make private' : 'Make public',
  );
  const visibilityIcon = $derived(image.isPublic ? 'ph:eye' : 'ph:eye-slash');

  const heroClass = (
    intent: 'default' | 'primary' | 'destructive' = 'default',
  ) => (isHero ? heroButtonOverrides({ intent }) : undefined);

  const {
    isLoading: visibilityIsLoading,
    error: updateVisibilityError,
    run: updateVisibility,
  } = ReactiveState(
    (imageId: string, isPublic: boolean) =>
      ApiClient.image.updateDetails(imageId, { isPublic }),
    { minExecutionTime: 300 },
  );

  const {
    isLoading: deleteImageIsLoading,
    error: deleteImageError,
    run: deleteImage,
  } = ReactiveState((imageId: string, preserveOnDisk: boolean) =>
    ApiClient.image.remove(imageId, preserveOnDisk),
  );

  const {
    isLoading: shareIsLoading,
    error: shareError,
    data: shareData,
    run: shareImage,
  } = ReactiveState<ShareResponse>(
    (imageId: string) => ApiClient.image.shareImage(imageId, {}),
    { minExecutionTime: 300 },
  );

  const isCopiedState = useAutoReset(1000);
  let deletePopoverOpen = $state(false);
  let collectionPopoverOpen = $state(false);

  const collectionPickerState = createCollectionPickerState();
  const createCollectionModalState = createCreateCollectionModalState();

  $effect(() => {
    collectionPickerState.setImage(image.id, image.collectionIds ?? []);
  });

  $effect(() => {
    if (collectionPopoverOpen) {
      collectionPickerState.load();
    }
  });

  const copyTooltip = $derived.by(() => {
    if ($shareIsLoading) return 'Generating...';
    if (isCopiedState.active) return 'Copied!';
    return 'Copy URL';
  });

  const handleDownload = () => downloadByLink(directLink, image.fileName);

  const handleVisibilityChange = async () => {
    const newValue = !image.isPublic;
    await updateVisibility(image.id, newValue);
    if ($updateVisibilityError) {
      toast.error('Failed to update visibility. Please try again later.');
      return;
    }
    image = { ...image, isPublic: newValue };
    historyFeedState.update(image.id, {
      attributes: { isPublic: newValue },
    });
  };

  const handleCopy = async () => {
    await shareImage(image.id);
    if ($shareError || !$shareData) {
      toast.error('Failed to generate share link. Please try again later.');
      return;
    }
    await navigator.clipboard.writeText(routes.share.fromResponse($shareData));
    isCopiedState.trigger();
  };

  const handleDelete = async (preserveOnDiskAfterDeletion: boolean) => {
    await deleteImage(image.id, preserveOnDiskAfterDeletion);
    if ($deleteImageError) {
      toast.error('Failed to delete image. Please try again later.');
      return;
    }
    historyFeedState.removeItem(image.id);
    deletePopoverOpen = false;
    await goto('/history');
    on?.imageDelete?.(image.id);
  };

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
  {#if $shareIsLoading}
    <div class={cn(iconClass, 'flex items-center justify-center')}>
      <Loader variant="minimal" size="xs" />
    </div>
  {:else if isCopiedState.active}
    <div in:scale={{ duration: 300, easing: cubicOut }}>
      <Icon
        icon="ph:check"
        class={cn(iconClass, 'text-green-600 dark:text-green-400')}
      />
    </div>
  {:else}
    <Icon icon="solar:link-linear" class={iconClass} />
  {/if}
{/snippet}

{#snippet deletePopoverContent()}
  <ImageDeletePopover
    loading={deleteImageIsLoading}
    close={() => (deletePopoverOpen = false)}
    confirm={({ preserveOnDiskAfterDeletion }) =>
      handleDelete(preserveOnDiskAfterDeletion)}
  />
{/snippet}

{#snippet downloadButton(position: ButtonPosition)}
  <ButtonGroupItem
    variant="primary"
    size="md"
    position={isHero ? 'only' : position}
    class={cn(
      isHero
        ? heroButtonOverrides({ intent: 'primary' })
        : 'gap-1.5 px-3 min-w-fit flex-3',
    )}
    onclick={handleDownload}
    aria-label="Download image"
    tooltip={compact && !isHero ? 'Download' : undefined}
  >
    <Icon
      icon="lucide:download"
      class={cn(
        isHero ? iconSizeVariants({ size: 'lg' }) : iconClass,
        'shrink-0',
      )}
    />
    {#if isHero || !compact}
      <span class={cn('font-medium truncate', isHero ? '' : 'text-xs')}
        >Download</span
      >
    {/if}
  </ButtonGroupItem>
{/snippet}

{#snippet visibilityButton(position: ButtonPosition)}
  <ButtonGroupItem
    variant="default"
    size="md"
    position={isHero ? 'only' : position}
    class={heroClass()}
    onclick={handleVisibilityChange}
    disabled={$visibilityIsLoading}
    aria-label={visibilityTooltip}
    tooltip={visibilityTooltip}
  >
    {@render loaderOrIcon(visibilityIcon, $visibilityIsLoading)}
  </ButtonGroupItem>
{/snippet}

{#snippet copyButton(position: ButtonPosition)}
  <ButtonGroupItem
    variant={isHero ? 'default' : 'secondary'}
    size="md"
    position={isHero ? 'only' : position}
    class={cn(heroClass(), !isHero && 'gap-1.5 px-2.5')}
    onclick={handleCopy}
    disabled={$shareIsLoading || isCopiedState.active}
    aria-label="Copy image URL"
    tooltip={copyTooltip}
  >
    {@render copyIconContent()}
  </ButtonGroupItem>
{/snippet}

{#snippet deleteButton(position: ButtonPosition)}
  <Overlay
    bind:open={deletePopoverOpen}
    variant="floating"
    contentProps={{ align: 'end' }}
    triggerClass={isHero ? '' : 'flex-1'}
  >
    {#snippet trigger()}
      <ButtonGroupItem
        variant="destructive"
        size="md"
        position={isHero ? 'only' : position}
        class={heroClass('destructive')}
        aria-label="Delete image"
        disabled={$deleteImageIsLoading}
        tooltip="Delete image"
      >
        <Icon icon="ph:trash" class={iconClass} />
      </ButtonGroupItem>
    {/snippet}
    {@render deletePopoverContent()}
  </Overlay>
{/snippet}

{#snippet collectionButton(position: ButtonPosition)}
  <Overlay
    bind:open={collectionPopoverOpen}
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
        class={heroClass()}
        aria-label="Add to collection"
        tooltip="Add to collection"
      >
        <Icon icon="ph:folder-simple" class={iconClass} />
      </ButtonGroupItem>
    {/snippet}
    <CollectionPicker
      pickerState={collectionPickerState}
      createModalState={createCollectionModalState}
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
    <div class={actionBarContainerVariants({ layout })}>
      {#each visibleButtons as button, i (button)}
        {@const position = getPosition(i, visibleButtons.length)}
        {#if button === 'download'}
          {@render renderButton(button, position)}
        {/if}
      {/each}
      <div class={actionBarSecondaryGroupVariants({ layout })}>
        {#each visibleButtons as button, i (button)}
          {@const position = getPosition(i, visibleButtons.length)}
          {#if button !== 'download'}
            {@render renderButton(button, position)}
          {/if}
        {/each}
      </div>
    </div>
  {:else}
    <ButtonGroup
      variant="glass"
      rounded="lg"
      size="md"
      gap="none"
      padding="none"
    >
      {#each visibleButtons as button, i (button)}
        {@const position = getPosition(i, visibleButtons.length)}
        {@render renderButton(button, position)}
      {/each}
    </ButtonGroup>
  {/if}
</TooltipProvider>
