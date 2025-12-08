<script lang="ts">
  import { ImageDeletePopover } from '@slink/feature/Image';
  import { Loader } from '@slink/feature/Layout';
  import {
    ButtonGroup,
    ButtonGroupItem,
    buttonGroupItemVariants,
  } from '@slink/ui/components';
  import { Overlay } from '@slink/ui/components/popover';
  import { Tooltip, TooltipProvider } from '@slink/ui/components/tooltip';

  import { goto } from '$app/navigation';
  import { useGlobalSettings } from '$lib/state/GlobalSettings.svelte.js';
  import { useUploadHistoryFeed } from '$lib/state/UploadHistoryFeed.svelte.js';
  import { downloadByLink } from '$lib/utils/http/downloadByLink';
  import { toast } from '$lib/utils/ui/toast-sonner.svelte.js';
  import { routes } from '$lib/utils/url/routes';
  import Icon from '@iconify/svelte';
  import { cubicOut } from 'svelte/easing';
  import { scale } from 'svelte/transition';

  import { ApiClient } from '@slink/api/Client';
  import { ReactiveState } from '@slink/api/ReactiveState';
  import type { ShareImageResponse } from '@slink/api/Resources/ImageResource';
  import type { ImageListingItem } from '@slink/api/Response';

  import { cn } from '@slink/utils/ui';

  type actionButton = 'download' | 'visibility' | 'share' | 'delete' | 'copy';
  interface Props {
    image: { id: string; fileName: string; isPublic: boolean };
    buttons?: actionButton[];
    compact?: boolean;
    on?: {
      imageDelete: (imageId: string) => void;
    };
  }

  let {
    image = $bindable(),
    buttons = ['download', 'visibility', 'share', 'delete'],
    compact = false,
    on,
  }: Props = $props();

  const historyFeedState = useUploadHistoryFeed();
  const globalSettingsManager = useGlobalSettings();

  const allowOnlyPublicImages = $derived(
    globalSettingsManager.settings?.image?.allowOnlyPublicImages || false,
  );

  const isButtonVisible = (button: actionButton) => {
    if (!buttons.includes(button)) return false;

    if (button === 'visibility' && allowOnlyPublicImages) {
      return false;
    }

    return true;
  };

  const {
    isLoading: visibilityIsLoading,
    error: updateVisibilityError,
    run: updateVisibility,
  } = ReactiveState(
    (imageId: string, isPublic: boolean) => {
      return ApiClient.image.updateDetails(imageId, {
        isPublic,
      });
    },
    { minExecutionTime: 300 },
  );

  const handleVisibilityChange = async (isPublic: boolean) => {
    await updateVisibility(image.id, isPublic);

    if ($updateVisibilityError) {
      toast.error('Failed to update visibility. Please try again later.');
      return;
    }

    image = { ...image, isPublic };

    historyFeedState.update(image.id, {
      attributes: { isPublic },
    } as ImageListingItem);
  };

  const {
    isLoading: deleteImageIsLoading,
    error: deleteImageError,
    run: deleteImage,
  } = ReactiveState((imageId: string, preserveOnDisk: boolean) => {
    return ApiClient.image.remove(imageId, preserveOnDisk);
  });

  const {
    isLoading: shareIsLoading,
    error: shareError,
    data: shareData,
    run: shareImage,
  } = ReactiveState<ShareImageResponse>(
    (imageId: string) => ApiClient.image.shareImage(imageId, {}),
    { minExecutionTime: 300 },
  );

  let isCopiedActive = $state(false);
  let deletePopoverOpen = $state(false);

  const handleCopy = async () => {
    await shareImage(image.id);

    if ($shareError || !$shareData) {
      toast.error('Failed to generate share link. Please try again later.');
      return;
    }

    const shareUrl = routes.share.fromResponse($shareData, { absolute: true });
    await navigator.clipboard.writeText(shareUrl);

    isCopiedActive = true;

    setTimeout(() => {
      isCopiedActive = false;
    }, 1000);
  };

  const confirmImageDeletion = async ({
    preserveOnDiskAfterDeletion,
  }: {
    preserveOnDiskAfterDeletion: boolean;
  }) => {
    await deleteImage(image.id, preserveOnDiskAfterDeletion);

    if ($deleteImageError) {
      toast.error('Failed to delete image. Please try again later.');
      return;
    }

    historyFeedState.removeItem(image.id);
    deletePopoverOpen = false;

    await goto('/history');
    on?.imageDelete(image.id);
  };

  const closeDeletePopover = () => {
    deletePopoverOpen = false;
  };

  let directLink = $derived(
    routes.image.view(image.fileName, undefined, { absolute: true }),
  );

  const visibleButtons = $derived(
    buttons.filter((button) => isButtonVisible(button)),
  );

  const getPosition = (index: number, total: number) => {
    if (total === 1) return 'only';
    if (index === 0) return 'first';
    if (index === total - 1) return 'last';
    return 'middle';
  };

  const iconClass = 'h-4 w-4';
</script>

<TooltipProvider delayDuration={300}>
  <ButtonGroup variant="glass" rounded="lg" size="lg" class="w-full shadow-sm">
    {#each visibleButtons as button, i (button)}
      {@const position = getPosition(i, visibleButtons.length)}

      {#if button === 'download'}
        <ButtonGroupItem
          variant="primary"
          size="lg"
          {position}
          class="gap-2 px-3 sm:px-4 min-w-fit flex-3"
          onclick={() => downloadByLink(directLink, image.fileName)}
          aria-label="Download image"
        >
          <Icon icon="lucide:download" class={cn(iconClass, 'shrink-0')} />
          {#if !compact}
            <span class="font-medium truncate text-xs sm:text-sm">Download</span
            >
          {/if}
        </ButtonGroupItem>
      {/if}

      {#if button === 'copy'}
        <ButtonGroupItem
          variant="secondary"
          size="lg"
          {position}
          class="gap-2 px-3"
          onclick={handleCopy}
          disabled={$shareIsLoading || isCopiedActive}
          aria-label="Copy image URL"
          tooltip={$shareIsLoading
            ? 'Generating...'
            : isCopiedActive
              ? 'Copied!'
              : 'Copy URL'}
        >
          {#if $shareIsLoading}
            <Loader variant="minimal" size="xs" />
          {:else if isCopiedActive}
            <div in:scale={{ duration: 300, easing: cubicOut }}>
              <Icon
                icon="ph:check"
                class={cn(iconClass, 'text-green-600 dark:text-green-400')}
              />
            </div>
          {:else}
            <Icon icon="ph:copy" class={iconClass} />
          {/if}
        </ButtonGroupItem>
      {/if}

      {#if button === 'visibility'}
        <ButtonGroupItem
          variant="default"
          size="lg"
          {position}
          onclick={() => handleVisibilityChange(!image.isPublic)}
          disabled={$visibilityIsLoading}
          aria-label={image.isPublic ? 'Make private' : 'Make public'}
          tooltip={image.isPublic ? 'Make private' : 'Make public'}
        >
          {#if $visibilityIsLoading}
            <Loader variant="minimal" size="xs" />
          {:else}
            <Icon
              icon={image.isPublic ? 'ph:eye' : 'ph:eye-slash'}
              class={iconClass}
            />
          {/if}
        </ButtonGroupItem>
      {/if}

      {#if button === 'share'}
        <Tooltip side="bottom" sideOffset={8} withArrow={false}>
          {#snippet trigger()}
            <a
              href="/help/faq#share-feature"
              class={cn(
                'flex-1',
                buttonGroupItemVariants({
                  variant: 'default',
                  size: 'lg',
                  position,
                }),
              )}
              aria-label="View sharing policy"
            >
              <Icon icon="ph:share-network" class={iconClass} />
            </a>
          {/snippet}
          Sharing policy
        </Tooltip>
      {/if}

      {#if button === 'delete'}
        <Overlay
          bind:open={deletePopoverOpen}
          variant="floating"
          contentProps={{ align: 'end' }}
          triggerClass="flex-1"
        >
          {#snippet trigger()}
            <ButtonGroupItem
              variant="destructive"
              size="lg"
              {position}
              aria-label="Delete image"
              disabled={$deleteImageIsLoading}
              tooltip="Delete image"
            >
              <Icon icon="ph:trash" class={iconClass} />
            </ButtonGroupItem>
          {/snippet}

          <ImageDeletePopover
            loading={deleteImageIsLoading}
            close={closeDeletePopover}
            confirm={confirmImageDeletion}
          />
        </Overlay>
      {/if}
    {/each}
  </ButtonGroup>
</TooltipProvider>
