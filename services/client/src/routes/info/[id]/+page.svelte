<script lang="ts">
  import {
    ImageActionBar,
    ImageDescription,
    ImagePlaceholder,
    ImageSizePicker,
    ImageTagManager,
  } from '@slink/feature/Image';
  import type { ImageParams, ImageSize } from '@slink/feature/Image';
  import { CopyContainer } from '@slink/feature/Text';

  import { page } from '$app/state';
  import { fly } from 'svelte/transition';

  import { ApiClient } from '@slink/api/Client';
  import { ReactiveState } from '@slink/api/ReactiveState';

  import { cn } from '@slink/utils/ui';
  import { printErrorsAsToastMessage } from '@slink/utils/ui/printErrorsAsToastMessage';

  import type { PageData } from './$types';

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

    const paramsString = Object.entries(params)
      .filter(
        ([_, value]) =>
          value !== false && value !== undefined && value !== null,
      )
      .map(([key, value]) => `${key}=${value}`)
      .join('&');

    return paramsString ? [url, paramsString].join('?') : url;
  };

  let params: Partial<ImageParams> = $state({});
  let directLink: string = $derived(
    formatImageUrl([page.url.origin, image.url], params),
  );

  const maxWidthClass = $derived.by(() => {
    const aspectRatio = image.width / image.height;

    if (aspectRatio > 2) {
      return 'max-w-4xl';
    } else if (aspectRatio > 1.5) {
      return 'max-w-3xl';
    } else if (aspectRatio > 1) {
      return 'max-w-2xl';
    } else if (aspectRatio > 0.7) {
      return 'max-w-xl';
    } else {
      return 'max-w-lg';
    }
  });

  const handleImageSizeChange = (
    value?: Partial<ImageSize & { crop?: boolean }>,
  ) => {
    let { width, height, crop, ...rest } = params;

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
    <div class={cn('w-full', maxWidthClass)}>
      <ImagePlaceholder src={image.url} metadata={image} stretch={false} />
    </div>

    <div class="grow max-w-md flex-shrink-0 space-y-8">
      <ImageActionBar {image} buttons={['download', 'visibility', 'delete']} />

      <ImageTagManager
        imageId={image.id}
        variant="neon"
        initialTags={data.imageTags}
      />

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
