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

    // Filter out false boolean values and create query params
    const paramsString = Object.entries(params)
      .filter(
        ([key, value]) =>
          value !== false && value !== undefined && value !== null,
      )
      .map(([key, value]) => `${key}=${value}`)
      .join('&');

    return paramsString ? [url, paramsString].join('?') : url;
  };

  let params: Partial<ImageParams> = $state({});
  let directLink: string = $derived(
    formatImageUrl([$page.url.origin, image.url], params),
  );

  const handleImageSizeChange = (
    value?: Partial<ImageSize & { crop?: boolean }>,
  ) => {
    let { width, height, crop, ...rest } = params;

    // If crop is explicitly false, remove it from params
    if (value?.crop === false) {
      params = {
        ...rest,
        ...(value.width && { width: value.width }),
        ...(value.height && { height: value.height }),
      };
    } else {
      params = {
        ...rest,
        ...value,
      };
    }
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
  <title>Image Details | Slink</title>
</svelte:head>

<main
  in:fly={{ y: 20, duration: 400, delay: 100 }}
  class="container mx-auto px-4 sm:px-6 lg:px-8 py-8"
>
  <div class="flex flex-col flex-wrap lg:flex-row gap-8">
    <div class="w-full max-w-2xl">
      <ImagePlaceholder src={image.url} metadata={image} stretch={false} />
    </div>

    <div class="w-full lg:w-80 flex-shrink-0 space-y-8">
      <ImageActionBar {image} />

      <div>
        <ImageDescription
          description={image.description}
          isLoading={$descriptionIsLoading}
          on={{
            change: (description: string) => handleSaveDescription(description),
          }}
        />
      </div>

      {#if image.supportsResize}
        <div>
          <div class="flex items-center gap-2 mb-4">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
              Resize
            </h2>
          </div>
          <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
            Apply resize to coppied image URL. The original image will remain
            unchanged.
          </p>
          <ImageSizePicker
            width={image.width}
            height={image.height}
            on={{ change: (size) => handleImageSizeChange(size) }}
          />
        </div>
      {/if}

      <div>
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">
          Share
        </h2>
        <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
          Use the link below to share or embed the image.
        </p>
        <CopyContainer value={directLink} />
      </div>
    </div>
  </div>
</main>
