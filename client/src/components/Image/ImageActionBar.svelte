<script lang="ts">
  import { createEventDispatcher } from 'svelte';

  import { goto } from '$app/navigation';
  import { page } from '$app/stores';
  import Icon from '@iconify/svelte';
  import { fly } from 'svelte/transition';

  import { ApiClient } from '@slink/api/Client';
  import { ReactiveState } from '@slink/api/ReactiveState';

  import { downloadByLink } from '@slink/utils/http/downloadByLink';
  import { toast } from '@slink/utils/ui/toast';

  import { Button, Modal, Tooltip } from '@slink/components/Common';
  import { Toggle } from '@slink/components/Form';

  export let image: {
    id: string;
    fileName: string;
    isPublic: boolean;
  };

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

  let deleteModalVisible = false;
  let preserveOnDiskAfterDeletion = false;

  const {
    isLoading: deleteImageIsLoading,
    error: deleteImageError,
    run: deleteImage,
  } = ReactiveState((imageId: string, preserveOnDisk: boolean) => {
    return ApiClient.image.remove(imageId, preserveOnDisk);
  });
  const handleDeleteImage = async () => {
    await deleteImage(image.id, preserveOnDiskAfterDeletion);

    if ($deleteImageError) {
      toast.error('Failed to delete image. Please try again later.');
      return;
    }

    await goto('/history');

    deleteModalVisible = false;
    dispatch('imageDeleted', image.id);
  };

  let isCopiedActive = false;
  const handleCopy = async () => {
    await navigator.clipboard.writeText(directLink);

    isCopiedActive = true;

    setTimeout(() => {
      isCopiedActive = false;
    }, 1000);
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
        color="dark"
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
        color="dark"
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
        color="dark"
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
        on:click={() => (deleteModalVisible = true)}
      >
        <Icon icon="solar:trash-bin-minimalistic-broken" class="h-5 w-5" />
      </Button>
      <Tooltip
        triggeredBy="[id^='open-delete-tooltip-{image.id}']"
        class="max-w-[10rem] p-2 text-center text-xs shadow-none"
        color="dark"
        placement="bottom"
      >
        Delete Image
      </Tooltip>
    </div>
  {/if}
</div>

<Modal
  variant="danger"
  align="top"
  bind:open={deleteModalVisible}
  loading={$deleteImageIsLoading}
  on:confirm={handleDeleteImage}
>
  <div slot="icon">
    <Icon
      icon="clarity:warning-standard-line"
      class="h-10 w-10 text-red-600/90"
    />
  </div>
  <p slot="title">Image Deletion</p>
  <div class="text-sm" slot="content">
    <p>
      Are you sure you want to delete this image? <br />
      This action cannot be undone.
    </p>
  </div>
  <div slot="extra" class="flex flex-grow flex-col justify-between gap-2">
    <div class="flex flex-grow items-center gap-2">
      <Toggle
        checked={!preserveOnDiskAfterDeletion}
        on:change={({ detail }) => (preserveOnDiskAfterDeletion = !detail)}
      />
      <Icon
        icon="ep:info-filled"
        id="preserve-on-disk-tooltip"
        class="hidden cursor-help xs:block"
      />
      <Tooltip
        triggeredBy="[id^='preserve-on-disk-tooltip']"
        class="max-w-[10rem] p-2 text-center text-[0.5em]"
        color="dark"
        placement="top"
      >
        Perform physical deletion of the image file, not just the database entry
      </Tooltip>
    </div>

    <span class="text-[0.5em]">
      {#if preserveOnDiskAfterDeletion}
        Preserve in Storage
      {:else}
        Delete from Storage
      {/if}
    </span>
  </div>
  <div slot="confirm" class="flex items-center justify-between">
    <span>Delete</span>
  </div>
</Modal>
