<script lang="ts">
  import { goto } from '$app/navigation';
  import { page } from '$app/stores';
  import Icon from '@iconify/svelte';
  import { fly } from 'svelte/transition';

  import { ApiClient } from '@slink/api/Client';
  import { ReactiveState } from '@slink/api/ReactiveState';

  import { downloadByLink } from '@slink/utils/http/downloadByLink.js';
  import { printErrorsAsToastMessage } from '@slink/utils/ui/printErrorsAsToastMessage';
  import { toast } from '@slink/utils/ui/toast';

  import {
    Button,
    CopyContainer,
    Modal,
    Tooltip,
  } from '@slink/components/Common';
  import { Toggle } from '@slink/components/Form';
  import {
    ImageDescription,
    type ImageParams,
    ImagePlaceholder,
    type ImageSize,
    ImageSizePicker,
  } from '@slink/components/Image';

  import type { PageData } from './$types';

  export let data: PageData;

  let params: Partial<ImageParams> = {};
  let directLink: string;

  const filterResizable = (mimeType: string) => {
    return !new RegExp('svg|gif|webp').test(mimeType);
  };

  const handleImageSizeChange = (value?: Partial<ImageSize>) => {
    let { width, height, ...rest } = params;

    params = {
      ...rest,
      ...value,
    };
  };

  const formatImageUrl = (
    url: string | string[],
    params: Partial<ImageParams>
  ) => {
    url = Array.isArray(url) ? url.join('') : url;

    if (!params || Object.keys(params).length === 0) {
      return url;
    }

    // go over all params and add them to the url as query params
    const paramsString = Object.entries(params)
      .map(([key, value]) => `${key}=${value}`)
      .join('&');

    return [url, paramsString].join('?');
  };

  const {
    isLoading: descriptionIsLoading,
    error: updateDescriptionError,
    run: updateDescription,
  } = ReactiveState((imageId: string, description: string) => {
    return ApiClient.image.updateDetails(imageId, {
      description,
    });
  });

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

  const handleSaveDescription = async (description: string) => {
    await updateDescription(data.id, description);

    if ($updateDescriptionError) {
      printErrorsAsToastMessage($updateDescriptionError);
      return;
    }

    data.description = description;
  };

  const handleVisibilityChange = async (isPublic: boolean) => {
    await updateVisibility(data.id, isPublic);

    if ($updateVisibilityError) {
      toast.error('Failed to update visibility. Please try again later.');
      return;
    }

    data.isPublic = isPublic;
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
    await deleteImage(data.id, preserveOnDiskAfterDeletion);

    if ($deleteImageError) {
      toast.error('Failed to delete image. Please try again later.');
      return;
    }

    await goto('/history');

    toast.success('Image deleted successfully');
  };

  $: directLink = formatImageUrl([$page.url.origin, data.url], params);
</script>

<svelte:head>
  <title>Detailed View | Slink</title>
</svelte:head>

<div
  in:fly={{ y: 100, duration: 500, delay: 100 }}
  class="flex justify-center p-2 sm:p-8"
>
  <div class="container flex flex-row flex-wrap justify-center gap-6">
    <div class="max-w-full flex-shrink">
      <div class="flex max-w-full justify-start">
        <ImagePlaceholder
          src={data.url}
          height={32}
          aspectRatio={data.height / data.width}
          metadata={data}
        />
      </div>
    </div>

    <div class="min-w-0 px-2">
      <div class="mb-12 flex items-center gap-2">
        <Button
          variant="primary"
          size="md"
          on:click={() => downloadByLink(directLink, data.fileName)}
        >
          <span class="mr-2 hidden text-sm font-light xs:block">Download</span>
          <Icon
            icon="material-symbols-light:download"
            slot="rightIcon"
            class="h-5 w-5"
          />
        </Button>

        <div>
          <Button
            variant="invisible"
            size="md"
            class="px-3 transition-colors"
            id="open-visibility-tooltip"
            on:click={() => handleVisibilityChange(!data.isPublic)}
            disabled={$visibilityIsLoading}
          >
            {#if $visibilityIsLoading}
              <Icon icon="mdi-light:loading" class="h-5 w-5 animate-spin" />
            {:else if data.isPublic}
              <Icon icon="ph:eye-light" class="h-5 w-5" />
            {:else}
              <Icon icon="ph:eye-slash-light" class="h-5 w-5" />
            {/if}
          </Button>
          <Tooltip
            triggeredBy="[id^='open-visibility-tooltip']"
            class="max-w-[10rem] p-2 text-center text-xs"
            color="dark"
            placement="bottom"
          >
            {#if data.isPublic}
              Make Private
            {:else}
              Make Public
            {/if}
          </Tooltip>
        </div>

        <div>
          <Button
            variant="invisible"
            size="md"
            class="px-3 transition-colors"
            id="open-share-tooltip"
            href="/help/faq#share-feature"
          >
            <Icon icon="mdi-light:share-variant" class="h-5 w-5" />
          </Button>
          <Tooltip
            triggeredBy="[id^='open-share-tooltip']"
            class="max-w-[10rem] p-2 text-center text-xs"
            color="dark"
            placement="bottom"
          >
            Share Policy
          </Tooltip>
        </div>

        <div>
          <Button
            variant="invisible"
            size="md"
            class="px-3 transition-colors hover:bg-button-danger hover:text-white"
            id="open-delete-tooltip"
            on:click={() => (deleteModalVisible = true)}
          >
            <Icon icon="solar:trash-bin-minimalistic-broken" class="h-5 w-5" />
          </Button>
          <Tooltip
            triggeredBy="[id^='open-delete-tooltip']"
            class="max-w-[10rem] p-2 text-center text-xs"
            color="dark"
            placement="bottom"
          >
            Delete Image
          </Tooltip>
        </div>
      </div>
      <p class="mb-4 mt-8 w-full">
        <ImageDescription
          description={data.description}
          isLoading={$descriptionIsLoading}
          on:descriptionChange={(e) => handleSaveDescription(e.detail)}
        />
      </p>
      <div class="mb-2 mt-8">
        <p class="my-2 text-xs font-extralight">
          Copy the direct image link to use it on your website or share it with
          others
        </p>
        <CopyContainer value={directLink} />
      </div>
      {#if filterResizable(data.mimeType)}
        <div class="mb-2 flex items-center gap-3">
          <ImageSizePicker
            width={data.width}
            height={data.height}
            on:change={(e) => handleImageSizeChange(e.detail)}
          />
          <Icon
            icon="ep:info-filled"
            id="open-resize-info-tooltip"
            class="hidden cursor-help xs:block"
          />
          <Tooltip
            triggeredBy="[id^='open-resize-info-tooltip']"
            class="max-w-[10rem] p-2 text-center text-xs"
            color="dark"
            placement="bottom"
          >
            Set the size before copying the image url
          </Tooltip>
        </div>
      {/if}
    </div>
  </div>
</div>

<Modal
  variant="danger"
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
