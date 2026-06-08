<script lang="ts">
  import {
    Banner,
    BannerAction,
    BannerContent,
    BannerIcon,
  } from '@slink/feature/Layout';
  import { UploadFormWithOptions, UploadSuccess } from '@slink/feature/Upload';
  import AutoGroupBanner from '@slink/feature/Upload/AutoGroupBanner.svelte';
  import MultiUploadProgress from '@slink/feature/Upload/MultiUploadProgress.svelte';
  import type { Visibility } from '@slink/feature/Upload/UploadOptions';

  import { page } from '$app/state';
  import { fade } from 'svelte/transition';

  import { useUploadPageState } from '@slink/lib/state/UploadPageState.svelte';

  import type { PageData } from './$types';

  interface Props {
    data: PageData;
  }

  let { data }: Props = $props();

  const state = useUploadPageState(data, page.url);

  const uploadsComplete = $derived(
    state.uploads.length > 0 &&
      state.uploads.every(
        (upload) =>
          upload.status === 'completed' ||
          upload.status === 'error' ||
          upload.status === 'cancelled',
      ),
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
      {#if state.showSuccess}
        <UploadSuccess
          onUploadAnother={() => state.dismissSuccess()}
          isGuestUser={!data.user}
        />
      {:else if state.isMultiUpload}
        <div class="space-y-4">
          {#if uploadsComplete && state.autoGroup.createdCollection}
            <AutoGroupBanner
              variant="created"
              collectionName={state.autoGroup.createdCollection.name}
              pending={state.autoGroup.undoPending}
              autoGroupEnabled={state.autoGroup.enabled}
              togglePending={state.autoGroup.pending}
              onView={() => state.handleViewAutoCollection()}
              onUndo={() => state.handleUndoAutoCollection()}
              onToggleAutoGroup={(value) => state.autoGroup.setEnabled(value)}
            />
          {:else if uploadsComplete && state.autoGroup.failed}
            <AutoGroupBanner variant="failed" />
          {/if}

          <MultiUploadProgress
            uploads={state.uploads}
            onCancel={() => state.handleCancelMultiUpload()}
            onRetryAll={() => state.handleRetryFailedUploads()}
            onGoBack={() => state.handleGoBackToUploadForm()}
            onViewUploads={state.showViewUploads
              ? () => state.handleViewUploads()
              : undefined}
          />
        </div>
      {:else}
        {#if !data.user}
          <div class="mb-8">
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
          </div>
        {/if}

        <UploadFormWithOptions
          disabled={state.disabled}
          processing={state.processing}
          allowMultiple={true}
          selectedTags={state.selectedTags}
          selectedCollections={state.selectedCollections}
          visibility={(data.defaultVisibility as Visibility) ?? 'private'}
          allowOnlyPublicImages={data.allowOnlyPublicImages}
          autoGroupBatchUploads={state.autoGroup.enabled}
          autoGroupPending={state.autoGroup.pending}
          onTagsChange={(tags) => state.setSelectedTags(tags)}
          onCollectionsChange={(collections) =>
            state.setSelectedCollections(collections)}
          onAutoGroupChange={(value) => state.autoGroup.setEnabled(value)}
          onchange={(files) => state.handleUpload(files)}
        />
      {/if}
    </div>
  </div>
</div>
