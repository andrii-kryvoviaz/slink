<script lang="ts">
  import { createEventDispatcher } from 'svelte';

  import { goto } from '$app/navigation';
  import { page } from '$app/stores';
  import Icon from '@iconify/svelte';
  import { fly } from 'svelte/transition';

  import { ApiClient } from '@slink/api/Client';
  import { ReactiveState } from '@slink/api/ReactiveState';
  import type { ImageDetailsResponse } from '@slink/api/Response';

  import { downloadByLink } from '@slink/utils/http/downloadByLink';
  import { toast } from '@slink/utils/ui/toast';

  import { ImageDeleteConfirmation } from '@slink/components/Feature/Image';
  import { Button } from '@slink/components/UI/Action';
  import { Tooltip } from '@slink/components/UI/Tooltip';

  export let image: ImageDetailsResponse;

  type actionButton = 'download' | 'visibility' | 'share' | 'delete' | 'copy';
  export let buttons: actionButton[] = [
    'download',
    'visibility',
    'share',
    'delete',
  ];

  const isButtonVisible = (button: actionButton) => buttons.includes(button);

  const dispatch = createEventDispatcher<{ imageDeleted: string }>();

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
    { minExecutionTime: 300 }
  );

  const handleVisibilityChange = async (isPublic: boolean) => {
    await updateVisibility(image.id, isPublic);

    if ($updateVisibilityError) {
      toast.error('Failed to update visibility. Please try again later.');
      return;
    }

    image.isPublic = isPublic;
  };

  const {
    isLoading: deleteImageIsLoading,
    error: deleteImageError,
    run: deleteImage,
  } = ReactiveState((imageId: string, preserveOnDisk: boolean) => {
    return ApiClient.image.remove(imageId, preserveOnDisk);
  });

  let isCopiedActive = false;
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

          toast.remove(image.id);

          await goto('/history');
          dispatch('imageDeleted', image.id);
        },
      },
    });
  };

  $: directLink = `${$page.url.origin}/image/${image.fileName}`;
</script>

<div class="flex items-center gap-2">
  {#if isButtonVisible('download')}
    <div>
      <Button
        variant="primary"
        size="md"
        on:click={() => downloadByLink(directLink, image.fileName)}
      >
        <span class="mr-2 hidden text-sm font-light xs:block">Download</span>
        <Icon
          icon="material-symbols-light:download"
          slot="rightIcon"
          class="h-5 w-5"
        />
      </Button>
    </div>
  {/if}

  {#if isButtonVisible('visibility')}
    <div>
      <Button
        variant="invisible"
        size="md"
        class="px-3 transition-colors"
        id="open-visibility-tooltip-{image.id}"
        on:click={() => handleVisibilityChange(!image.isPublic)}
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
      <Tooltip
        triggeredBy="[id^='open-visibility-tooltip-{image.id}'"
        class="max-w-[10rem] p-2 text-center text-xs shadow-none"
        placement="bottom"
      >
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
      <Button
        variant="invisible"
        size="md"
        class="px-3 transition-colors"
        id="open-share-tooltip-{image.id}"
        href="/help/faq#share-feature"
      >
        <Icon icon="mdi-light:share-variant" class="h-5 w-5" />
      </Button>
      <Tooltip
        triggeredBy="[id^='open-share-tooltip-{image.id}']"
        class="max-w-[10rem] p-2 text-center text-xs shadow-none"
        placement="bottom"
      >
        Share Policy
      </Tooltip>
    </div>
  {/if}

  {#if isButtonVisible('copy')}
    <div>
      <Button
        variant="invisible"
        size="md"
        class="px-3 transition-colors"
        id="open-copy-tooltip-{image.id}"
        on:click={handleCopy}
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
      <Tooltip
        triggeredBy="[id^='open-copy-tooltip-{image.id}']"
        class="max-w-[10rem] p-2 text-center text-xs shadow-none"
        placement="bottom"
      >
        Copy URL
      </Tooltip>
    </div>
  {/if}

  {#if isButtonVisible('delete')}
    <div>
      <Button
        variant="invisible"
        size="md"
        class="px-3 transition-colors hover:bg-button-danger hover:text-white"
        id="open-delete-tooltip-{image.id}"
        on:click={handleImageDeletion}
      >
        <Icon icon="solar:trash-bin-minimalistic-2-linear" class="h-5 w-5" />
      </Button>
      <Tooltip
        triggeredBy="[id^='open-delete-tooltip-{image.id}']"
        class="max-w-[10rem] p-2 text-center text-xs shadow-none"
        placement="bottom"
      >
        Delete Image
      </Tooltip>
    </div>
  {/if}
</div>
