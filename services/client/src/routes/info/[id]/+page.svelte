<script lang="ts">
  import { ApiClient } from '@slink/api';
  import { ImageCollectionList } from '@slink/feature/Collection';
  import {
    BookmarkersPanel,
    FilterPicker,
    FormatPicker,
    ImageActionBar,
    ImageDescription,
    ImagePlaceholder,
    ImageSizePicker,
    ShareLinkCopy,
    ViewCountBadge,
    VisibilityBadge,
  } from '@slink/feature/Image';
  import type { ImageOutputFormat, ImageParams } from '@slink/feature/Image';
  import { type ImageFilter, getCssFilter } from '@slink/feature/Image';
  import { ImageTagList } from '@slink/feature/Tag';
  import { Notice } from '@slink/feature/Text';
  import { Shortcut } from '@slink/ui/components';
  import { Select } from '@slink/ui/components';

  import { routes } from '$lib/utils/url/routes';
  import Icon from '@iconify/svelte';
  import { fly } from 'svelte/transition';

  import { ReactiveState } from '@slink/api/ReactiveState';
  import type { Tag } from '@slink/api/Resources/TagResource';
  import type { ShareResponse } from '@slink/api/Response';
  import type { CollectionReference } from '@slink/api/Response/Collection/CollectionResponse';

  import type { License } from '@slink/lib/enum/License';

  import { cn } from '@slink/utils/ui';
  import { printErrorsAsToastMessage } from '@slink/utils/ui/printErrorsAsToastMessage';

  import type { PageData } from './$types';

  interface Props {
    data: PageData;
  }

  let { data }: Props = $props();
  let prevImageId = data.image.id;
  let image = $state(data.image);
  let licenses = $derived((data as { licenses?: License[] }).licenses ?? []);
  let licensingEnabled = $derived(
    (data as { licensingEnabled?: boolean }).licensingEnabled ?? false,
  );
  let selectedLicense = $state(data.image.license ?? '');

  let imageTags: Tag[] = $state(data.imageTags ?? []);
  let imageCollections: CollectionReference[] = $state(
    data.image.collections ?? [],
  );

  $effect(() => {
    if (data.image.id !== prevImageId) {
      prevImageId = data.image.id;
      image = data.image;
      imageTags = data.imageTags ?? [];
      imageCollections = data.image.collections ?? [];
      selectedLicense = data.image.license ?? '';
    }
  });

  let actionBarImage = $state({
    id: image.id,
    fileName: image.fileName,
    isPublic: image.isPublic,
    collectionIds: imageCollections.map((c) => c.id),
    tagIds: imageTags.map((t) => t.id),
  });

  $effect(() => {
    actionBarImage = {
      id: image.id,
      fileName: image.fileName,
      isPublic: image.isPublic,
      collectionIds: imageCollections.map((c) => c.id),
      tagIds: imageTags.map((t) => t.id),
    };
  });

  $effect(() => {
    if (actionBarImage.isPublic !== image.isPublic) {
      image = { ...image, isPublic: actionBarImage.isPublic };
    }
  });

  const handleTagChange = (_imageId: string, tags: Tag[]) => {
    imageTags = tags;
  };

  const handleCollectionChange = (
    _imageId: string,
    collections: CollectionReference[],
  ) => {
    imageCollections = collections;
  };

  let unsignedParams: Partial<ImageParams> = $state({});
  let selectedFormat: ImageOutputFormat = $state('original');
  let selectedFilter: ImageFilter = $state('none');

  const originalFormat = $derived.by(() => {
    const fileName = image.fileName;
    const lastDotIndex = fileName.lastIndexOf('.');
    return lastDotIndex !== -1 ? fileName.substring(lastDotIndex + 1) : '';
  });

  const applyFormatToFileName = (
    fileName: string,
    format: ImageOutputFormat,
  ): string => {
    if (format === 'original') return fileName;
    const lastDotIndex = fileName.lastIndexOf('.');
    if (lastDotIndex === -1) return `${fileName}.${format}`;
    return `${fileName.substring(0, lastDotIndex)}.${format}`;
  };

  let formattedFileName = $derived(
    applyFormatToFileName(image.fileName, selectedFormat),
  );
  let directLink: string = $derived(
    routes.image.view(formattedFileName, {}, { absolute: true }),
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
    isLoading: isSharingImage,
    error: shareImageError,
    data: shareImageData,
    run: shareImage,
  } = ReactiveState<ShareResponse>(
    (
      imageId: string,
      params: {
        width?: number;
        height?: number;
        crop?: boolean;
        format?: string;
        filter?: string;
      },
    ) => {
      return ApiClient.image.shareImage(imageId, params);
    },
    {
      minExecutionTime: 200,
      debounce: 300,
    },
  );

  let shareUrl: string | undefined = $state(undefined);

  $effect(() => {
    shareImage(image.id, {
      ...unsignedParams,
      format: selectedFormat,
      filter: selectedFilter === 'none' ? undefined : selectedFilter,
    });
  });

  $effect(() => {
    if ($shareImageError) {
      printErrorsAsToastMessage($shareImageError);
      shareUrl = undefined;
      return;
    }

    const response = $shareImageData;
    if (!response) {
      return;
    }

    shareUrl = routes.share.fromResponse(response);
  });

  const handleImageSizeChange = (value?: Partial<ImageParams>) => {
    unsignedParams = value ?? {};
  };

  const handleFormatChange = (format: ImageOutputFormat) => {
    selectedFormat = format;
  };

  const handleFilterChange = (filter: ImageFilter) => {
    selectedFilter = filter;
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

  const licenseOptions = $derived(
    licenses.map((license: License) => ({
      value: license.id,
      label: license.title,
    })),
  );

  const {
    isLoading: licenseIsLoading,
    error: updateLicenseError,
    run: updateLicense,
  } = ReactiveState((imageId: string, license: string) => {
    return ApiClient.image.updateDetails(imageId, { license });
  });

  const handleSaveLicense = async (license: string) => {
    await updateLicense(image.id, license);

    if ($updateLicenseError) {
      printErrorsAsToastMessage($updateLicenseError);
      return;
    }

    image = { ...image, license };
  };

  $effect(() => {
    if (selectedLicense !== (image.license ?? '') && selectedLicense) {
      handleSaveLicense(selectedLicense);
    }
  });
</script>

<svelte:head>
  <title>Image Details | Slink</title>
</svelte:head>

<main
  in:fly={{ y: 20, duration: 400, delay: 100 }}
  class="container mx-auto px-4 sm:px-6 lg:px-8 py-8"
>
  <div class="flex flex-col lg:flex-row gap-8">
    <div
      class={cn(
        'w-full relative group transition-[filter] duration-300',
        'lg:sticky lg:top-8 lg:self-start',
        maxWidthClass,
      )}
      style:filter={getCssFilter(selectedFilter)}
    >
      <ImagePlaceholder
        src={image.url}
        metadata={image}
        showOpenInNewTab={false}
      />
      <div
        class="absolute top-4 left-4 flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200"
      >
        <VisibilityBadge isPublic={image.isPublic} variant="overlay" />
        <ViewCountBadge count={image.views} variant="overlay" />
      </div>
      <div class="mt-4">
        <ImageActionBar
          bind:image={actionBarImage}
          buttons={['download', 'collection', 'tag', 'visibility', 'delete']}
          layout="hero"
          on={{
            tagChange: handleTagChange,
            collectionChange: handleCollectionChange,
          }}
        />
      </div>
      {#if imageTags.length > 0 || imageCollections.length > 0}
        <div class="mt-4 flex flex-col gap-2">
          {#if imageTags.length > 0}
            <ImageTagList
              imageId={image.id}
              variant="neon"
              showImageCount={false}
              removable={false}
              initialTags={imageTags}
              maxVisible={4}
              disableHover={true}
            />
          {/if}
          {#if imageCollections.length > 0}
            <ImageCollectionList
              collections={imageCollections}
              maxVisible={4}
            />
          {/if}
        </div>
      {/if}
    </div>

    <div class="grow max-w-md min-w-0 space-y-8">
      <BookmarkersPanel imageId={image.id} count={image.bookmarkCount} />

      <ImageDescription
        description={image.description ?? ''}
        isLoading={$descriptionIsLoading}
        on={{ change: handleSaveDescription }}
      />

      {#if licensingEnabled && licenses.length > 0}
        <div>
          <div class="flex items-center gap-2 mb-2">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
              License
            </h2>
            {#if $licenseIsLoading}
              <span class="text-xs text-gray-500 dark:text-gray-400"
                >Saving...</span
              >
            {/if}
          </div>
          <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">
            Choose how others can use this image
          </p>
          <Select
            class="w-full"
            items={licenseOptions}
            bind:value={selectedLicense}
            placeholder="Select a license..."
          />
        </div>
      {/if}

      {#if image.supportsResize}
        <div>
          <div class="flex items-center gap-2 mb-4">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
              Resize
            </h2>
          </div>
          <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
            Adjust dimensions for the shared link
          </p>
          <ImageSizePicker
            width={image.width}
            height={image.height}
            on={{ change: (size) => handleImageSizeChange(size) }}
          />
        </div>
      {/if}

      {#if image.supportsResize}
        <div>
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">
            Filter
          </h2>
          <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
            Apply a color filter to the shared link
          </p>
          <FilterPicker
            imageUrl={image.url}
            value={selectedFilter}
            on={{ change: handleFilterChange }}
          />
        </div>
      {/if}

      <div>
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">
          Share
        </h2>
        <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
          Resize, filter, and format changes apply only to the shared link. The
          original image remains unchanged.
        </p>
        <Notice variant="info" size="xs" class="mb-4">
          <span class="flex items-center justify-between">
            <span class="flex items-center gap-2">
              <Icon icon="lucide:clipboard-copy" class="h-3.5 w-3.5 shrink-0" />
              <span>Select option to copy</span>
            </span>
            <span
              class="flex items-center gap-1.5 pl-3 border-l border-violet-300 dark:border-violet-600"
            >
              <span class="text-[10px] uppercase tracking-wide opacity-60"
                >Quick</span
              >
              <Shortcut control key="C" size="xs" />
            </span>
          </span>
        </Notice>

        {#if image.supportsFormatConversion}
          <div class="mb-4">
            <span
              class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
            >
              Output Format
            </span>
            <FormatPicker
              value={selectedFormat}
              {originalFormat}
              isAnimated={image.isAnimated}
              on={{ change: handleFormatChange }}
            />
          </div>
        {/if}

        <ShareLinkCopy
          value={directLink}
          {shareUrl}
          imageAlt={image.id}
          isLoading={$isSharingImage}
        />
      </div>
    </div>
  </div>
</main>
