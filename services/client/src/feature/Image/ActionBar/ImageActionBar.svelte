<script lang="ts">
  import { ImageDeletePopover } from '@slink/feature/Image';
  import { Loader } from '@slink/feature/Layout';
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

  type actionButton = 'download' | 'visibility' | 'share' | 'delete' | 'copy';
  interface Props {
    image: { id: string; fileName: string; isPublic: boolean };
    buttons?: actionButton[];
    on?: {
      imageDelete: (imageId: string) => void;
    };
  }

  let {
    image = $bindable(),
    buttons = ['download', 'visibility', 'share', 'delete'],
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

  const baseButtonClass =
    'group relative flex items-center justify-center transition-all duration-200 ease-out focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50';

  const actionButtonClass = `${baseButtonClass} h-11 w-11 sm:h-9 sm:w-9 rounded-xl bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm border border-gray-200/60 dark:border-gray-700/60 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 hover:bg-white dark:hover:bg-gray-800 hover:border-gray-300 dark:hover:border-gray-600 hover:shadow-sm active:scale-95 focus-visible:ring-blue-500/30`;

  const primaryButtonClass = `${baseButtonClass} h-11 sm:h-9 px-4 sm:px-3 gap-2 rounded-xl bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-medium shadow-sm hover:shadow-md active:scale-95 focus-visible:ring-blue-500/30 whitespace-nowrap`;

  const secondaryButtonClass = `${baseButtonClass} h-11 sm:h-9 px-3 sm:px-2.5 gap-2 rounded-xl bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600 active:scale-95 focus-visible:ring-gray-500/30 whitespace-nowrap`;

  const destructiveButtonClass = `${baseButtonClass} h-11 w-11 sm:h-9 sm:w-9 rounded-xl bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm border border-gray-200/60 dark:border-gray-700/60 text-gray-500 dark:text-gray-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 hover:border-red-200 dark:hover:border-red-800/50 active:scale-95 focus-visible:ring-red-500/30`;
</script>

<TooltipProvider delayDuration={300}>
  <div class="flex flex-wrap items-center gap-2 w-full">
    {#if isButtonVisible('download')}
      <button
        class={primaryButtonClass}
        onclick={() => downloadByLink(directLink, image.fileName)}
        aria-label="Download image"
        type="button"
      >
        <Icon icon="lucide:download" class="h-4 w-4 sm:h-3.5 sm:w-3.5" />
        <span class="text-sm sm:text-xs font-medium">Download</span>
      </button>
    {/if}

    {#if isButtonVisible('copy')}
      <Tooltip side="bottom" sideOffset={8} withArrow={false}>
        {#snippet trigger()}
          <button
            class={secondaryButtonClass}
            onclick={handleCopy}
            disabled={$shareIsLoading || isCopiedActive}
            aria-label="Copy image URL"
            type="button"
          >
            {#if $shareIsLoading}
              <Loader variant="minimal" size="xs" />
            {:else if isCopiedActive}
              <div in:scale={{ duration: 300, easing: cubicOut }}>
                <Icon
                  icon="ph:check"
                  class="h-4 w-4 sm:h-3.5 sm:w-3.5 text-green-600 dark:text-green-400"
                />
              </div>
            {:else}
              <Icon icon="ph:copy" class="h-4 w-4 sm:h-3.5 sm:w-3.5" />
            {/if}
          </button>
        {/snippet}
        {$shareIsLoading
          ? 'Generating...'
          : isCopiedActive
            ? 'Copied!'
            : 'Copy URL'}
      </Tooltip>
    {/if}

    {#if isButtonVisible('visibility')}
      <Tooltip side="bottom" sideOffset={8} withArrow={false}>
        {#snippet trigger()}
          <button
            class={actionButtonClass}
            onclick={() => handleVisibilityChange(!image.isPublic)}
            disabled={$visibilityIsLoading}
            aria-label={image.isPublic ? 'Make private' : 'Make public'}
            type="button"
          >
            {#if $visibilityIsLoading}
              <Loader variant="minimal" size="xs" />
            {:else}
              <Icon
                icon={image.isPublic ? 'ph:eye' : 'ph:eye-slash'}
                class="h-4 w-4 sm:h-3.5 sm:w-3.5"
              />
            {/if}
          </button>
        {/snippet}
        {image.isPublic ? 'Make private' : 'Make public'}
      </Tooltip>
    {/if}

    {#if isButtonVisible('share')}
      <Tooltip side="bottom" sideOffset={8} withArrow={false}>
        {#snippet trigger()}
          <a
            href="/help/faq#share-feature"
            class={actionButtonClass}
            aria-label="View sharing policy"
          >
            <Icon icon="ph:share-network" class="h-4 w-4 sm:h-3.5 sm:w-3.5" />
          </a>
        {/snippet}
        Sharing policy
      </Tooltip>
    {/if}

    {#if isButtonVisible('delete')}
      <Overlay
        bind:open={deletePopoverOpen}
        variant="floating"
        contentProps={{ align: 'end' }}
      >
        {#snippet trigger()}
          <Tooltip side="bottom" sideOffset={8} withArrow={false}>
            {#snippet trigger()}
              <button
                class={destructiveButtonClass}
                aria-label="Delete image"
                type="button"
                disabled={$deleteImageIsLoading}
              >
                <Icon icon="ph:trash" class="h-4 w-4 sm:h-3.5 sm:w-3.5" />
              </button>
            {/snippet}
            Delete image
          </Tooltip>
        {/snippet}

        <ImageDeletePopover
          loading={deleteImageIsLoading}
          close={closeDeletePopover}
          confirm={confirmImageDeletion}
        />
      </Overlay>
    {/if}
  </div>
</TooltipProvider>
