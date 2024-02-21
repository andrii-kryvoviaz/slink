<script lang="ts">
  import type { PageData } from './$types';
  import { fly } from 'svelte/transition';
  import Icon from '@iconify/svelte';
  import { Tooltip } from '@slink/components/Common';
  import { ApiClient } from '@slink/api/Client';
  import { toast } from '@slink/store/toast';
  import { ValidationException } from '@slink/api/Exceptions/ValidationException';
  import {
    ImageDescription,
    type ImageParams,
    ImagePlaceholder,
    type ImageSize,
    ImageSizePicker,
  } from '@slink/components/Image';
  import { CopyContainer } from '@slink/components/Common';

  export let data: PageData;

  let params: Partial<ImageParams> = {};
  let url: string;

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

  const formatImageUrl = (url: string, params: Partial<ImageParams>) => {
    if (!params || Object.keys(params).length === 0) {
      return url;
    }

    // go over all params and add them to the url as query params
    const paramsString = Object.entries(params)
      .map(([key, value]) => `${key}=${value}`)
      .join('&');

    return `${url}?${paramsString}`;
  };

  let descriptionIsLoading: boolean = false;

  const handleSaveDescription = async (description: string) => {
    try {
      descriptionIsLoading = true;

      await ApiClient.image.updateDetails(data.id, {
        description,
      });

      data.description = description;

      toast.success('Description has been updated');
    } catch (error: any) {
      if (error instanceof ValidationException) {
        error.violations.forEach((violation) => {
          toast.error(violation.message);
        });
      } else {
        toast.error('Something went wrong');
      }
    } finally {
      descriptionIsLoading = false;
    }
  };

  $: url = formatImageUrl(data.url, params);
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
          isLoading={descriptionIsLoading}
          on:descriptionChange={(e) => handleSaveDescription(e.detail)}
        />
      </p>
      <div class="mb-2">
        <CopyContainer value={url} />
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
