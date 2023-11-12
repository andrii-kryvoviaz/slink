<script lang="ts">
  import Button from '@slink/components/Shared/Action/Button.svelte';
  import ImagePlaceholder from '../../../components/Image/Preview/ImagePlaceholder.svelte';
  import type { PageData } from './$types';
  import { fly } from 'svelte/transition';
  import CopyContainer from '@slink/components/Shared/Action/CopyContainer.svelte';
  import ImageSizePicker from '@slink/components/Image/Action/ImageSizePicker.svelte';
  import type {
    ImageParams,
    ImageSize,
  } from '@slink/components/Image/Types/ImageParams';
  import Icon from '@iconify/svelte';
  import { Tooltip } from 'flowbite-svelte';

  export let data: PageData;

  let params: Partial<ImageParams> = {};
  let url: string;

  const filterResizable = (mimeType: string) => {
    return !new RegExp('svg|gif').test(mimeType);
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

  $: url = formatImageUrl(data.url, params);
</script>

<div
  in:fly={{ y: 100, duration: 500, delay: 100 }}
  class="flex justify-center p-8"
>
  <div class="container flex max-w-[1280px] flex-row flex-wrap gap-6">
    <div class="flex-shrink">
      <div class="flex justify-start">
        <ImagePlaceholder
          src={data.url}
          height={32}
          aspectRatio={data.height / data.width}
          metadata={data}
        />
      </div>

      <Button
        class="mr-2 mt-4"
        variant="secondary"
        size="md"
        rounded="full"
        target="_self"
        href="/">Upload More</Button
      >
    </div>

    <div class="flex-grow px-2">
      <p
        class="mb-4 mt-8 border-l-4 border-slate-800 pl-2 text-xl font-bold text-slate-900 dark:border-slate-400 dark:text-slate-100"
      >
        {data.description || 'No description provided yet.'}
      </p>
      <div class="mb-4">
        <CopyContainer value={url} />
      </div>
      {#if filterResizable(data.mimeType)}
        <div class="mb-4 flex items-center gap-3">
          <ImageSizePicker
            width={data.width}
            height={data.height}
            onChange={handleImageSizeChange}
          />
          <Icon
            icon="ep:info-filled"
            id="open-resize-info-tooltip"
            class="cursor-help"
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
