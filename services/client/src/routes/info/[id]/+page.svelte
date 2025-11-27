<script lang="ts">
  import {
    ImageActionBar,
    ImageDescription,
    ImagePlaceholder,
    ImageSizePicker,
    ImageTagManager,
    ShareLinkCopy,
  } from '@slink/feature/Image';
  import type { ImageParams } from '@slink/feature/Image';
  import { Notice } from '@slink/feature/Text';
  import { Shortcut } from '@slink/ui/components';

  import { page } from '$app/state';
  import { fly } from 'svelte/transition';

  import { ApiClient } from '@slink/api/Client';
  import { ReactiveState } from '@slink/api/ReactiveState';
  import type { SignedImageParams } from '@slink/api/Resources/ImageResource';
  import type { Tag } from '@slink/api/Resources/TagResource';

  import { useUploadHistoryFeed } from '@slink/lib/state/UploadHistoryFeed.svelte';

  import { cn } from '@slink/utils/ui';
  import { printErrorsAsToastMessage } from '@slink/utils/ui/printErrorsAsToastMessage';

  import type { PageData } from './$types';

  interface Props {
    data: PageData;
  }

  let { data }: Props = $props();
  let image = $state(data.image);

  const historyFeedState = useUploadHistoryFeed();

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

  let unsignedParams: Partial<ImageParams> = $state({});
  let directLink: string = $derived(
    formatImageUrl([page.url.origin, image.url], {}),
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

  const {
    isLoading: isSigningParams,
    error: signParamsError,
    data: signedParamsData,
    run: signImageParams,
  } = ReactiveState<SignedImageParams>(
    (
      imageId: string,
      params: { width?: number; height?: number; crop?: boolean },
    ) => {
      return ApiClient.image.signImageParams(imageId, params);
    },
    {
      minExecutionTime: 200,
    },
  );

  const handleImageSizeChange = (value?: Partial<ImageParams>) => {
    unsignedParams = value ?? {};
  };

  const handleBeforeCopy = async (): Promise<string | void> => {
    if (Object.keys(unsignedParams).length === 0) {
      return;
    }

    await signImageParams(image.id, unsignedParams);

    if ($signParamsError) {
      printErrorsAsToastMessage($signParamsError);
      return;
    }

    const signedParams = $signedParamsData;
    if (signedParams) {
      const params = {
        width: signedParams.width ?? undefined,
        height: signedParams.height ?? undefined,
        crop: signedParams.crop || undefined,
        s: signedParams.signature,
      };
      return formatImageUrl([page.url.origin, image.url], params);
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

  const handleTagsUpdate = (updatedTags: Tag[]) => {
    historyFeedState.update(image.id, {
      tags: updatedTags,
    });
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

    <div class="grow max-w-md shrink-0 space-y-8">
      <ImageActionBar {image} buttons={['download', 'visibility', 'delete']} />

      <ImageTagManager
        imageId={image.id}
        variant="neon"
        initialTags={data.imageTags}
        on={{
          tagsUpdate: handleTagsUpdate,
        }}
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
        <Notice variant="info" size="xs" class="mb-4">
          Copy the direct link or use the dropdown for other formats. Press
          <span class="inline-flex mx-1"
            ><Shortcut control key="C" size="xs" /></span
          >
          to copy.
        </Notice>
        <ShareLinkCopy
          value={directLink}
          imageAlt={image.fileName}
          isLoading={$isSigningParams}
          onBeforeCopy={handleBeforeCopy}
        />
      </div>
    </div>
  </div>
</main>
