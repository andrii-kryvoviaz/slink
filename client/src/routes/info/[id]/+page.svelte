<script lang="ts">
  import { page } from '$app/stores';
  import Icon from '@iconify/svelte';
  import { fly } from 'svelte/transition';

  import { ApiClient } from '@slink/api/Client';
  import { ReactiveState } from '@slink/api/ReactiveState';

  import { printErrorsAsToastMessage } from '@slink/utils/ui/printErrorsAsToastMessage';

  import { CopyContainer, Tooltip } from '@slink/components/Common';
  import {
    ImageActionBar,
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

  const handleSaveDescription = async (description: string) => {
    await updateDescription(data.id, description);

    if ($updateDescriptionError) {
      printErrorsAsToastMessage($updateDescriptionError);
      return;
    }

    data.description = description;
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
    <div class="flex w-fit max-w-full justify-start">
      <ImagePlaceholder
        src={data.url}
        aspectRatio={data.height / data.width}
        metadata={data}
      />
    </div>

    <div class="min-w-0 px-2">
      <div class="mb-12">
        <ImageActionBar image={data} />
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
      {#if data.supportsResize}
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
