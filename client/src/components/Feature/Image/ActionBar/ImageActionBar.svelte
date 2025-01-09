<script lang="ts">
  import type { ImageListingItem } from '@slink/api/Response';

  import { goto } from '$app/navigation';
  import { page } from '$app/state';
  import Icon from '@iconify/svelte';
  import { fly } from 'svelte/transition';

  import { ApiClient } from '@slink/api/Client';
  import { ReactiveState } from '@slink/api/ReactiveState';

  import { useUploadHistoryFeed } from '@slink/lib/state/UploadHistoryFeed.svelte';

  import { downloadByLink } from '@slink/utils/http/downloadByLink';
  import { toast } from '@slink/utils/ui/toast';

  import { ImageDeleteConfirmation } from '@slink/components/Feature/Image';
  import { Button } from '@slink/components/UI/Action';
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
</script>

<div class="flex items-center gap-2">
  {#if isButtonVisible('download')}
    <div>
      <Button
        variant="primary"
        size="md"
        onclick={() => downloadByLink(directLink, image.fileName)}
      >
        <span class="mr-2 hidden text-sm font-light xs:block">Download</span>
        {#snippet rightIcon()}
          <Icon icon="material-symbols-light:download" class="h-5 w-5" />
        {/snippet}
      </Button>
    </div>
  {/if}

  {#if isButtonVisible('visibility')}
    <div>
      <Tooltip size="xs" side="bottom">
        {#snippet trigger()}
          <Button
            variant="invisible"
            size="md"
            class="px-3 transition-colors"
            onclick={() => handleVisibilityChange(!image.isPublic)}
            disabled={$visibilityIsLoading}
          >
            {#if $visibilityIsLoading}
              <Icon icon="mdi-light:loading" class="h-5 w-5 animate-spin" />
            {:else if image.isPublic}
              <Icon icon="ph:eye-light" class="h-5 w-5" />
            {:else}
              <Icon icon="ph:eye-slash-light" class="h-5 w-5" />
            {/if}
          </Button>
        {/snippet}

        {#if image.isPublic}
          Make Private
        {:else}
          Make Public
        {/if}
      </Tooltip>
    </div>
  {/if}

  {#if isButtonVisible('share')}
    <div>
      <Tooltip size="xs" side="bottom">
        {#snippet trigger()}
          <Button
            variant="invisible"
            size="md"
            class="px-3 transition-colors"
            href="/help/faq#share-feature"
          >
            <Icon icon="mdi-light:share-variant" class="h-5 w-5" />
          </Button>
        {/snippet}

        Sharing Policy
      </Tooltip>
    </div>
  {/if}

  {#if isButtonVisible('copy')}
    <div>
      <Tooltip size="xs" side="bottom">
        {#snippet trigger()}
          <Button
            variant="invisible"
            size="md"
            class="px-3 transition-colors"
            onclick={handleCopy}
            disabled={isCopiedActive}
          >
            {#if isCopiedActive}
              <div in:fly={{ duration: 300 }}>
                <Icon icon="mdi-light:check" class="h-5 w-5" />
              </div>
            {:else}
              <div in:fly={{ duration: 300 }}>
                <Icon icon="lets-icons:copy-light" class="h-5 w-5" />
              </div>
            {/if}
          </Button>
        {/snippet}

        Copy URL
      </Tooltip>
    </div>
  {/if}

  {#if isButtonVisible('delete')}
    <div>
      <Tooltip size="xs" side="bottom">
        {#snippet trigger()}
          <Button
            variant="invisible"
            size="md"
            class="px-3 transition-colors hover:bg-button-danger hover:text-white"
            onclick={handleImageDeletion}
          >
            <Icon
              icon="solar:trash-bin-minimalistic-2-linear"
              class="h-5 w-5"
            />
          </Button>
        {/snippet}

        Delete Image
      </Tooltip>
    </div>
  {/if}
</div>
