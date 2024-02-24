<script lang="ts">
  import { page } from '$app/stores';
  import Icon from '@iconify/svelte';
  import { fly } from 'svelte/transition';

  import { ApiClient } from '@slink/api/Client';
  import { ReactiveState } from '@slink/api/ReactiveState';

  import { printErrorsAsToastMessage } from '@slink/utils/ui/printErrorsAsToastMessage';
  import { toast } from '@slink/utils/ui/toast';

  import { Tooltip } from '@slink/components/Common';
  import { CopyContainer } from '@slink/components/Common';
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
    reset: resetUpdateDescriptionState,
  } = ReactiveState((imageId: number, description: string) => {
    return ApiClient.image.updateDetails(imageId, {
      description,
    });
  });

  const {
    isLoading: visibilityIsLoading,
    error: updateVisibilityError,
    run: updateVisibility,
    reset: resetUpdateVisibilityState,
  } = ReactiveState((imageId: number, isPublic: boolean) => {
    return ApiClient.image.updateDetails(imageId, {
      isPublic,
    });
  });

  const handleSaveDescription = async (description: string) => {
    await updateDescription(data.id, description);

    if ($updateDescriptionError) {
      printErrorsAsToastMessage($updateDescriptionError);
      resetUpdateDescriptionState();
      return;
    }

    data.description = description;
  };

  const handleVisibilityChange = async (isPublic: boolean) => {
    await updateVisibility(data.id, isPublic);

    if ($updateVisibilityError) {
      toast.error('Failed to update visibility. Please try again later.');
      resetUpdateVisibilityState();
      return;
    }

    data.isPublic = isPublic;
  };

  $: directLink = formatImageUrl([$page.url.origin, data.url], params);
</script>

<div
  in:fly={{ y: 100, duration: 500, delay: 100 }}
  class="flex justify-center p-2 sm:p-8"
>
  <div class="container flex flex-row flex-wrap gap-6">
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

    <div class="min-w-0 flex-grow px-2">
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
          others.
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
      <div class="mt-8">
        <p class="my-2 text-xs font-extralight">
          Make image publicly available under <a
            class="font-normal text-primary hover:underline"
            href="/">listing</a
          >
          page, so anyone can see it.
          <span class="mt-1 block text-[0.8em] font-extralight">
            <strong>*</strong> This will not affect the direct link. Therefore, it
            is always accessible to anyone who holds the link.
          </span>
        </p>
        <div class="mt-2 flex items-center">
          <Toggle
            checked={data.isPublic}
            disabled={$visibilityIsLoading}
            on:change={({ detail }) => handleVisibilityChange(detail)}
          />
          <p class="ml-2 text-sm font-extralight">
            {#if $visibilityIsLoading}
              <Icon icon="mdi-light:loading" class="h-4 w-4 animate-spin" />
            {:else}
              {data.isPublic ? 'Public' : 'Private'}
            {/if}
          </p>
        </div>
      </div>
    </div>
  </div>
</div>
