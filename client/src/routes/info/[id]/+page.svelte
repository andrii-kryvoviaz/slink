<script lang="ts">
  import type { PageData } from './$types';

  import { page } from '$app/stores';
  import Icon from '@iconify/svelte';
  import { fly } from 'svelte/transition';

  import { ApiClient } from '@slink/api/Client';
  import { ReactiveState } from '@slink/api/ReactiveState';

  import { printErrorsAsToastMessage } from '@slink/utils/ui/printErrorsAsToastMessage';

  import {
    ImageActionBar,
    ImageDescription,
    type ImageParams,
    ImagePlaceholder,
    type ImageSize,
    ImageSizePicker,
  } from '@slink/components/Feature/Image';
  import { CopyContainer } from '@slink/components/UI/Action';
  import { Tooltip } from '@slink/components/UI/Tooltip';

  interface Props {
    data: PageData;
  }

  let { data }: Props = $props();
  let image = $state(data.image);

  const formatImageUrl = (
    url: string | string[],
    params: Partial<ImageParams>,
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

  let params: Partial<ImageParams> = $state({});
  let directLink: string = $derived(
    formatImageUrl([$page.url.origin, image.url], params),
  );

  const handleImageSizeChange = (value?: Partial<ImageSize>) => {
    let { width, height, ...rest } = params;

    params = {
      ...rest,
      ...value,
    };
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
    await updateDescription(image.id, description);

    if ($updateDescriptionError) {
      printErrorsAsToastMessage($updateDescriptionError);
      return;
    }

    image = { ...image, description };
  };
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
      <ImagePlaceholder src={image.url} metadata={image} />
    </div>

    <div class="min-w-0 px-2">
      <div class="mb-12">
        <ImageActionBar {image} />
      </div>

      <div class="mb-4 mt-8 w-full">
        <ImageDescription
          description={image.description}
          isLoading={$descriptionIsLoading}
          on={{
            change: (description: string) => handleSaveDescription(description),
          }}
        />
      </div>
      <div class="mb-2 mt-8">
        <p class="my-2 text-xs font-extralight">
          Copy this link to use on your website or share with others
        </p>
        <CopyContainer value={directLink} />
      </div>
      {#if image.supportsResize}
        <div class="mb-2 flex items-center gap-3">
          <ImageSizePicker
            width={image.width}
            height={image.height}
            on={{ change: (size) => handleImageSizeChange(size) }}
          />
          <Tooltip size="xs" side="top" align="end">
            {#snippet trigger()}
              <Icon icon="ep:info-filled" class="hidden cursor-help xs:block" />
            {/snippet}
            Adjust the size before copying the URL
          </Tooltip>
        </div>
      {/if}
    </div>
  </div>
</div>
