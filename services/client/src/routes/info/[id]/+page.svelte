<script lang="ts">
  import { ImageCollectionList } from '@slink/feature/Collection';
  import {
    BookmarkersPanel,
    FilterPicker,
    ImageActionBar,
    ImageDescription,
    ImagePlaceholder,
    ImageSizePicker,
    ViewCountBadge,
    VisibilityBadge,
  } from '@slink/feature/Image';
  import { getCssFilter } from '@slink/feature/Image';
  import { LicenseCard } from '@slink/feature/Image';
  import { Card as ShareCard } from '@slink/feature/Image';
  import { ImageTagList } from '@slink/feature/Tag';

  import { fly } from 'svelte/transition';

  import { cn } from '@slink/utils/ui';

  import type { PageData } from './$types';
  import { createImageInfoPageState } from './ImageInfoPageState.svelte';

  interface Props {
    data: PageData;
  }

  let { data }: Props = $props();

  const state = createImageInfoPageState({ getData: () => data });
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
        state.maxWidthClass,
      )}
      style:filter={getCssFilter(state.selectedFilter)}
    >
      <ImagePlaceholder
        src={state.image.url}
        metadata={state.image}
        showOpenInNewTab={false}
      />
      <div
        class="absolute top-4 left-4 flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200"
      >
        <VisibilityBadge isPublic={state.image.isPublic} variant="overlay" />
        <ViewCountBadge count={state.image.views} variant="overlay" />
      </div>
      <div class="mt-4">
        <ImageActionBar
          bind:image={state.actionBarImage}
          buttons={['download', 'collection', 'tag', 'visibility', 'delete']}
          layout="hero"
          on={{
            tagChange: state.handleTagChange,
            collectionChange: state.handleCollectionChange,
          }}
        />
      </div>
      {#if state.imageTags.length > 0 || state.imageCollections.length > 0}
        <div class="mt-4 flex flex-col gap-2">
          {#if state.imageTags.length > 0}
            <ImageTagList
              imageId={state.image.id}
              variant="neon"
              showImageCount={false}
              removable={false}
              initialTags={state.imageTags}
              maxVisible={4}
              disableHover={true}
            />
          {/if}
          {#if state.imageCollections.length > 0}
            <ImageCollectionList
              collections={state.imageCollections}
              maxVisible={4}
            />
          {/if}
        </div>
      {/if}
    </div>

    <div class="grow max-w-md min-w-0 space-y-8">
      <BookmarkersPanel
        imageId={state.image.id}
        count={state.image.bookmarkCount}
      />

      <ImageDescription
        description={state.image.description ?? ''}
        isLoading={state.descriptionIsLoading}
        on={{ change: state.handleSaveDescription }}
      />

      <LicenseCard
        imageId={state.image.id}
        license={state.image.license}
        licenses={data.licenses ?? []}
        licensingEnabled={data.licensingEnabled ?? false}
        on={{ licenseSaved: state.handleLicenseSaved }}
      />

      {#if state.image.supportsResize}
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
            width={state.image.width}
            height={state.image.height}
            on={{ change: state.handleImageSizeChange }}
          />
        </div>
      {/if}

      {#if state.image.supportsResize}
        <div>
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">
            Filter
          </h2>
          <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
            Apply a color filter to the shared link
          </p>
          <FilterPicker
            imageUrl={state.image.url}
            value={state.selectedFilter}
            on={{ change: state.handleFilterChange }}
          />
        </div>
      {/if}

      <ShareCard
        image={state.image}
        filter={state.selectedFilter}
        resizeParams={state.unsignedParams}
      />
    </div>
  </div>
</main>
