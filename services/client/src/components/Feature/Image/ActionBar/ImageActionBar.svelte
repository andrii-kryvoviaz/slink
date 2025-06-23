<script lang="ts">
  import type { ImageListingItem } from '@slink/api/Response';

  import { goto } from '$app/navigation';
  import { page } from '$app/state';
  import Icon from '@iconify/svelte';
  import { cubicOut } from 'svelte/easing';
  import { scale } from 'svelte/transition';

  import { ApiClient } from '@slink/api/Client';
  import { ReactiveState } from '@slink/api/ReactiveState';

  import { useUploadHistoryFeed } from '@slink/lib/state/UploadHistoryFeed.svelte';

  import { downloadByLink } from '@slink/utils/http/downloadByLink';
  import { toast } from '@slink/utils/ui/toast.svelte';

  import { ImageDeleteConfirmation } from '@slink/components/Feature/Image';
  import { Loader } from '@slink/components/UI/Loader';
  import { Tooltip } from '@slink/components/UI/Tooltip';

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

  const isButtonVisible = (button: actionButton) => buttons.includes(button);

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

  let isCopiedActive = $state(false);
  const handleCopy = async () => {
    await navigator.clipboard.writeText(directLink);

    isCopiedActive = true;

    setTimeout(() => {
      isCopiedActive = false;
    }, 1000);
  };

  const handleImageDeletion = () => {
    toast.component(ImageDeleteConfirmation, {
      id: image.id,
      props: {
        image,
        loading: deleteImageIsLoading,
        close: () => toast.remove(image.id),
        confirm: async ({ preserveOnDiskAfterDeletion }) => {
          await deleteImage(image.id, preserveOnDiskAfterDeletion);

          if ($deleteImageError) {
            toast.error('Failed to delete image. Please try again later.');
            return;
          }

          historyFeedState.removeItem(image.id);

          toast.remove(image.id);

          await goto('/history');
          on?.imageDelete(image.id);
        },
      },
    });
  };

  let directLink = $derived(`${page.url.origin}/image/${image.fileName}`);

  const actionButtonClass =
    'group relative flex h-12 w-12 sm:h-10 sm:w-10 items-center justify-center rounded-lg sm:rounded-xl bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm border border-gray-200/50 dark:border-gray-700/50 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 hover:bg-white dark:hover:bg-gray-800 hover:border-gray-300 dark:hover:border-gray-600 transition-all duration-200 ease-out hover:scale-[1.02] active:scale-[0.98] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/20 focus-visible:ring-offset-2';

  const primaryButtonClass =
    'group relative flex h-12 sm:h-10 items-center gap-1.5 sm:gap-2 px-3 sm:px-4 rounded-lg sm:rounded-xl bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-medium transition-all duration-200 ease-out focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/20 focus-visible:ring-offset-2 whitespace-nowrap';

  const destructiveButtonClass =
    'group relative flex h-12 w-12 sm:h-10 sm:w-10 items-center justify-center rounded-lg sm:rounded-xl bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm border border-gray-200/50 dark:border-gray-700/50 text-gray-500 dark:text-gray-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 hover:border-red-200 dark:hover:border-red-800/50 transition-all duration-200 ease-out hover:scale-[1.02] active:scale-[0.98] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500/20 focus-visible:ring-offset-2';
</script>

<div
  class="flex items-center sm:justify-between w-full sm:w-fit gap-2 sm:gap-3 rounded-xl sm:rounded-2xl"
>
  {#if isButtonVisible('download')}
    <button
      class={primaryButtonClass}
      onclick={() => downloadByLink(directLink, image.fileName)}
      aria-label="Download image"
      type="button"
    >
      <Icon icon="lucide:download" class="h-3.5 w-3.5 sm:h-4 sm:w-4" />
      <span class="text-xs sm:text-sm sm:inline whitespace-nowrap"
        >Download</span
      >
    </button>
  {/if}

  <div class="flex items-center gap-1.5 sm:gap-2">
    {#if isButtonVisible('visibility')}
      <Tooltip side="bottom" sideOffset={8}>
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
                class="h-3.5 w-3.5 sm:h-4 sm:w-4 transition-transform duration-200"
              />
            {/if}
          </button>
        {/snippet}
        {image.isPublic ? 'Make private' : 'Make public'}
      </Tooltip>
    {/if}

    {#if isButtonVisible('copy')}
      <Tooltip side="bottom" sideOffset={8}>
        {#snippet trigger()}
          <button
            class={actionButtonClass}
            onclick={handleCopy}
            disabled={isCopiedActive}
            aria-label="Copy image URL"
            type="button"
          >
            {#if isCopiedActive}
              <div in:scale={{ duration: 300, easing: cubicOut }}>
                <Icon
                  icon="ph:check"
                  class="h-3.5 w-3.5 sm:h-4 sm:w-4 text-green-600 dark:text-green-400"
                />
              </div>
            {:else}
              <Icon
                icon="ph:copy"
                class="h-3.5 w-3.5 sm:h-4 sm:w-4 transition-transform duration-200"
              />
            {/if}
          </button>
        {/snippet}
        {isCopiedActive ? 'Copied!' : 'Copy URL'}
      </Tooltip>
    {/if}

    {#if isButtonVisible('share')}
      <Tooltip side="bottom" sideOffset={8}>
        {#snippet trigger()}
          <a
            href="/help/faq#share-feature"
            class={actionButtonClass}
            aria-label="View sharing policy"
          >
            <Icon
              icon="ph:share-network"
              class="h-3.5 w-3.5 sm:h-4 sm:w-4 transition-transform duration-200"
            />
          </a>
        {/snippet}
        Sharing policy
      </Tooltip>
    {/if}
  </div>

  {#if isButtonVisible('delete')}
    <Tooltip side="bottom" sideOffset={8}>
      {#snippet trigger()}
        <button
          class={destructiveButtonClass}
          onclick={handleImageDeletion}
          aria-label="Delete image"
          type="button"
        >
          <Icon
            icon="ph:trash"
            class="h-3.5 w-3.5 sm:h-4 sm:w-4 transition-transform duration-200"
          />
        </button>
      {/snippet}
      Delete image
    </Tooltip>
  {/if}
</div>
