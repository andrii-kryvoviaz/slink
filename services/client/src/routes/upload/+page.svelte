<script lang="ts">
  import { ApiClient } from '@slink/api';
  import { BannerContainer } from '@slink/feature/Layout';
  import * as Share from '@slink/feature/Share';
  import type { ShareState } from '@slink/feature/Share';
  import {
    CollectionPrompt,
    ExifBanner,
    GuestBanner,
    Success,
    useExifNotice,
  } from '@slink/feature/Upload';
  import * as UploadProgress from '@slink/feature/Upload/Progress';
  import * as Uploader from '@slink/feature/Upload/Uploader';
  import { Button } from '@slink/ui/components/button';
  import { untrack } from 'svelte';

  import { page } from '$app/state';
  import Icon from '@iconify/svelte';
  import { fade } from 'svelte/transition';

  import { useUploadPageState } from '@slink/lib/state/UploadPageState.svelte';

  import type { PageData } from './$types';

  interface Props {
    data: PageData;
  }

  let { data }: Props = $props();

  const uploadState = useUploadPageState(data, page.url);

  const exifNotice = useExifNotice();

  let share: ShareState | null = $state(null);
  let lastSharedCollectionId: string | null = $state(null);

  $effect(() => {
    const collection = uploadState.createdCollection;

    untrack(() => {
      if (!collection) {
        share = null;
        lastSharedCollectionId = null;
        return;
      }

      if (collection.id === lastSharedCollectionId) {
        return;
      }

      lastSharedCollectionId = collection.id;
      share = Share.createShare({
        fetchShare: () => ApiClient.collection.share(collection.id),
      });
    });
  });

  const completedCount = $derived(
    uploadState.uploads.filter((upload) => upload.status === 'completed')
      .length,
  );
  const failedCount = $derived(
    uploadState.uploads.filter((upload) => upload.status === 'error').length,
  );
  const isCompleted = $derived(
    completedCount + failedCount === uploadState.uploads.length,
  );
  const hasErrors = $derived(failedCount > 0);
</script>

<svelte:head>
  <title>Upload | Slink</title>
</svelte:head>

<div class="min-h-full">
  <div class="container mx-auto p-6 pt-16">
    <div
      in:fade={{ duration: 500, delay: 100 }}
      class="w-full max-w-2xl mx-auto"
    >
      {#if uploadState.showSuccess}
        <Success
          onUploadAnother={() => uploadState.dismissSuccess()}
          isGuestUser={!data.user}
        />
      {:else}
        {@const showGuestNotice = !data.user}
        {#if showGuestNotice || exifNotice.visible}
          <BannerContainer class="mb-8">
            {#if showGuestNotice}
              <GuestBanner
                allowGuestUploads={data.globalSettings?.access
                  ?.allowGuestUploads}
              />
            {/if}

            <ExifBanner />
          </BannerContainer>
        {/if}

        {#if uploadState.isMultiUpload}
          <div class="space-y-3">
            {#if uploadState.showCollectionBanner}
              <CollectionPrompt
                count={completedCount}
                created={uploadState.createdCollection}
                pending={uploadState.collectionPending}
                {share}
                onCreate={(name) => uploadState.createCollection(name)}
                onView={() => uploadState.handleViewCreatedCollection()}
              />
            {/if}
            <UploadProgress.Root
              items={uploadState.uploads}
              class="space-y-6 p-6"
            >
              <UploadProgress.Header>
                <UploadProgress.Title>
                  {#if isCompleted}
                    Complete
                  {:else}
                    Uploading
                  {/if}
                </UploadProgress.Title>
                <UploadProgress.Summary />

                {#snippet actions()}
                  {#if isCompleted && hasErrors}
                    <Button
                      variant="soft-red"
                      size="sm"
                      rounded="full"
                      spacing="relaxed"
                      onclick={() => uploadState.handleRetryFailedUploads()}
                    >
                      <Icon icon="ph:arrow-clockwise" class="w-4 h-4" />
                      Retry Failed
                    </Button>
                  {/if}

                  {#if !isCompleted}
                    <Button
                      variant="glass"
                      size="sm"
                      rounded="full"
                      spacing="relaxed"
                      onclick={() => uploadState.handleCancelMultiUpload()}
                    >
                      <Icon icon="ph:x" class="w-4 h-4" />
                      Cancel
                    </Button>
                  {:else}
                    <Button
                      variant="glass"
                      size="sm"
                      rounded="full"
                      spacing="relaxed"
                      onclick={() => uploadState.handleGoBackToUploadForm()}
                    >
                      <Icon icon="ph:upload-simple" class="w-4 h-4" />
                      Upload more
                    </Button>
                  {/if}
                {/snippet}
              </UploadProgress.Header>

              <div class="space-y-3" in:fade={{ duration: 400, delay: 100 }}>
                <UploadProgress.Value size="md" animated shimmer>
                  {#snippet caption()}
                    Overall Progress
                  {/snippet}
                </UploadProgress.Value>

                <UploadProgress.Meter />
              </div>

              <UploadProgress.List>
                <UploadProgress.Footer
                  onViewAll={uploadState.showViewUploads
                    ? () => uploadState.handleViewUploads()
                    : undefined}
                />
              </UploadProgress.List>
            </UploadProgress.Root>
          </div>
        {:else if uploadState.singleUploadItem}
          <UploadProgress.Root
            items={[uploadState.singleUploadItem]}
            class="flex min-h-64 flex-col items-center justify-center p-6 text-center"
          >
            <div class="absolute top-4 right-4">
              <Button
                variant="glass"
                size="sm"
                rounded="full"
                spacing="relaxed"
                onclick={() => uploadState.handleCancelSingleUpload()}
              >
                <Icon icon="ph:x" class="w-4 h-4" />
                Cancel
              </Button>
            </div>

            <UploadProgress.Value size="lg" animated shimmer>
              {#snippet caption()}
                Upload in progress
              {/snippet}
            </UploadProgress.Value>

            <UploadProgress.Meter variant="edge" />
          </UploadProgress.Root>
        {:else}
          <div class="space-y-4">
            <Uploader.Root
              disabled={uploadState.disabled}
              allowMultiple={true}
              onchange={(files) => uploadState.handleUpload(files)}
            >
              <Uploader.Idle />
              <Uploader.DragOverlay />
            </Uploader.Root>

            <Uploader.Toolbar
              disabled={uploadState.disabled}
              processing={uploadState.processing}
              selectedTags={uploadState.selectedTags}
              selectedCollections={uploadState.selectedCollections}
              visibility={data.uploadPolicy.defaultVisibility ?? 'private'}
              allowOnlyPublicImages={data.uploadPolicy.allowOnlyPublicImages}
              onTagsChange={(tags) => uploadState.setSelectedTags(tags)}
              onCollectionsChange={(collections) =>
                uploadState.setSelectedCollections(collections)}
            />
          </div>
        {/if}
      {/if}
    </div>
  </div>
</div>
