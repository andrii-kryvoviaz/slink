<script lang="ts">
  import { ApiClient } from '@slink/api';
  import {
    Banner,
    BannerAction,
    BannerContainer,
    BannerContent,
    BannerIcon,
  } from '@slink/feature/Layout';
  import * as Share from '@slink/feature/Share';
  import type { ShareState } from '@slink/feature/Share';
  import {
    ExifPrivacyBanner,
    MultiUploadProgress,
    UploadCollectionBanner,
    UploadFormWithOptions,
    UploadSuccess,
    isExifNoticeVisible,
  } from '@slink/feature/Upload';
  import type { Visibility } from '@slink/feature/Upload/UploadOptions';
  import { untrack } from 'svelte';

  import { page } from '$app/state';
  import { fade } from 'svelte/transition';

  import { useUploadPageState } from '@slink/lib/state/UploadPageState.svelte';

  import type { PageData } from './$types';

  interface Props {
    data: PageData;
  }

  let { data }: Props = $props();

  const uploadState = useUploadPageState(data, page.url);

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
        <UploadSuccess
          onUploadAnother={() => uploadState.dismissSuccess()}
          isGuestUser={!data.user}
        />
      {:else if uploadState.isMultiUpload}
        <div class="space-y-3">
          {#if uploadState.showCollectionBanner}
            <UploadCollectionBanner
              count={completedCount}
              created={uploadState.createdCollection}
              pending={uploadState.collectionPending}
              {share}
              onCreate={(name) => uploadState.createCollection(name)}
              onView={() => uploadState.handleViewCreatedCollection()}
            />
          {/if}
          <MultiUploadProgress
            uploads={uploadState.uploads}
            onCancel={() => uploadState.handleCancelMultiUpload()}
            onRetryAll={() => uploadState.handleRetryFailedUploads()}
            onGoBack={() => uploadState.handleGoBackToUploadForm()}
            onViewUploads={uploadState.showViewUploads
              ? () => uploadState.handleViewUploads()
              : undefined}
          />
        </div>
      {:else}
        {@const showGuestNotice = !data.user}
        {@const showExifNotice = isExifNoticeVisible(
          data.stripExifMetadata,
          data.exifOverride,
          page.data.settings.banners.hideExifKeptNotice,
        )}
        {#if showGuestNotice || showExifNotice}
          <BannerContainer class="mb-8">
            {#if showGuestNotice}
              {#if data.globalSettings?.access?.allowGuestUploads}
                <Banner variant="info">
                  {#snippet icon()}
                    <BannerIcon variant="info" icon="ph:upload-simple" />
                  {/snippet}
                  {#snippet content()}
                    <BannerContent
                      title="Guest Upload"
                      description="You can upload images without an account, but consider signing in to manage them"
                    />
                  {/snippet}
                  {#snippet action()}
                    <BannerAction
                      variant="info"
                      href="/profile/login"
                      text="Sign In"
                    />
                  {/snippet}
                </Banner>
              {:else}
                <Banner variant="warning">
                  {#snippet icon()}
                    <BannerIcon variant="warning" icon="ph:lock-simple" />
                  {/snippet}
                  {#snippet content()}
                    <BannerContent
                      title="Sign in to continue"
                      description="Upload and manage your images securely"
                    />
                  {/snippet}
                  {#snippet action()}
                    <BannerAction
                      variant="warning"
                      href="/profile/login"
                      text="Get Started"
                    />
                  {/snippet}
                </Banner>
              {/if}
            {/if}

            <ExifPrivacyBanner
              stripExifMetadata={data.stripExifMetadata}
              exifOverride={data.exifOverride}
            />
          </BannerContainer>
        {/if}

        <UploadFormWithOptions
          disabled={uploadState.disabled}
          processing={uploadState.processing}
          allowMultiple={true}
          selectedTags={uploadState.selectedTags}
          selectedCollections={uploadState.selectedCollections}
          visibility={(data.defaultVisibility as Visibility) ?? 'private'}
          allowOnlyPublicImages={data.allowOnlyPublicImages}
          onTagsChange={(tags) => uploadState.setSelectedTags(tags)}
          onCollectionsChange={(collections) =>
            uploadState.setSelectedCollections(collections)}
          onchange={(files) => uploadState.handleUpload(files)}
        />
      {/if}
    </div>
  </div>
</div>
