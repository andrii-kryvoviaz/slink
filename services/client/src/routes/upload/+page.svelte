<script lang="ts">
  import {
    Banner,
    BannerAction,
    BannerContent,
    BannerIcon,
  } from '@slink/feature/Layout';
  import { UploadForm, UploadSuccess } from '@slink/feature/Upload';
  import MultiUploadProgress from '@slink/feature/Upload/MultiUploadProgress.svelte';

  import { goto } from '$app/navigation';
  import { fade } from 'svelte/transition';

  import { ApiClient } from '@slink/api/Client';
  import { ReactiveState } from '@slink/api/ReactiveState';
  import type { UploadedImageResponse } from '@slink/api/Response';

  import {
    MultiUploadService,
    type UploadItem,
  } from '@slink/lib/services/multi-upload.service';
  import { useUploadHistoryFeed } from '@slink/lib/state/UploadHistoryFeed.svelte';

  import { printErrorsAsToastMessage } from '@slink/utils/ui/printErrorsAsToastMessage';

  import type { PageData } from './$types';

  interface Props {
    data: PageData;
  }

  let { data }: Props = $props();

  let showSuccess = $state(false);
  let isMultiUpload = $state(false);
  let uploads: UploadItem[] = $state([]);
  let multiUploadService = new MultiUploadService();

  const {
    isLoading,
    data: uploadedImage,
    error: uploadError,
    run: uploadImage,
    reset: resetUploadImage,
  } = ReactiveState<UploadedImageResponse>((file: File) => {
    if (data.globalSettings?.access?.allowGuestUploads && !data.user) {
      return ApiClient.image.guestUpload(file);
    }

    return ApiClient.image.upload(file);
  });

  const { isLoading: pageIsChanging, run: redirectToInfo } = ReactiveState(
    (imageId: string) => goto(`/info/${imageId}`),
  );

  const handleUpload = async (files: File[]) => {
    if (files.length === 1) {
      await uploadImage(files[0]);
    } else {
      await handleMultiUpload(files);
    }
  };

  const handleMultiUpload = async (files: File[]) => {
    isMultiUpload = true;
    uploads = multiUploadService.createUploadItems(files);

    const { successful, failed } = await multiUploadService.uploadFiles(
      uploads,
      {
        isGuest: data.globalSettings?.access?.allowGuestUploads && !data.user,
        onProgress: (_item) => {
          uploads = [...uploads];
        },
        onError: (_item, error) => {
          console.error('Upload error for file:', _item.file.name, error);
        },
      },
    );

    if (failed.length === 0 && successful.length > 0) {
      if (data.user) {
        const historyFeedState = useUploadHistoryFeed();

        const imageIds = successful.map((item) => item.result!.id);
        const images = await ApiClient.image.getImagesByIds(imageIds);
        images.data.forEach((image) => historyFeedState.addItem(image));

        await goto('/history');
      } else {
        data.globalSettings?.access?.allowUnauthenticatedAccess
          ? await goto('/explore')
          : (showSuccess = true);
      }
    }
  };

  const handleCancelMultiUpload = () => {
    multiUploadService.cancelAllUploads();
    isMultiUpload = false;
    uploads = [];
  };

  const handleGoBackToUploadForm = () => {
    isMultiUpload = false;
    uploads = [];
  };

  const handleRetryFailedUploads = async () => {
    const failedUploads = uploads.filter((item) => item.status === 'error');
    if (failedUploads.length === 0) return;

    failedUploads.forEach((item) => {
      item.status = 'pending';
      item.progress = 0;
      item.error = undefined;
      item.errorDetails = undefined;
    });

    uploads = [...uploads];

    await multiUploadService.uploadFiles(failedUploads, {
      isGuest: data.globalSettings?.access?.allowGuestUploads && !data.user,
      onProgress: (_item) => {
        uploads = [...uploads];
      },
      onError: (_item, error) => {
        console.error('Retry upload error for file:', _item.file.name, error);
      },
    });
  };

  const successHandler = async (response: UploadedImageResponse) => {
    if (data.user) {
      const historyFeedState = useUploadHistoryFeed();

      await redirectToInfo(response.id);

      const images = await ApiClient.image.getImagesByIds([response.id]);
      images.data.forEach((image) => historyFeedState.addItem(image));
    } else {
      data.globalSettings?.access?.allowUnauthenticatedAccess
        ? await goto('/explore')
        : (showSuccess = true);
    }
  };

  const errorHandler = printErrorsAsToastMessage;

  $effect(() => {
    $uploadedImage && successHandler($uploadedImage);
  });

  $effect(() => {
    if (!$uploadError) return;
    errorHandler($uploadError);
    resetUploadImage();
  });

  let processing = $derived($isLoading || $pageIsChanging || isMultiUpload);
  let disabled = $derived(
    processing ||
      (!data.user && !data.globalSettings?.access?.allowGuestUploads),
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
      {#if showSuccess}
        <UploadSuccess
          onUploadAnother={() => (showSuccess = false)}
          isGuestUser={!data.user}
        />
      {:else if isMultiUpload}
        <MultiUploadProgress
          {uploads}
          onCancel={handleCancelMultiUpload}
          onRetryAll={handleRetryFailedUploads}
          onGoBack={handleGoBackToUploadForm}
        />
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

        <UploadForm
          {disabled}
          {processing}
          onchange={handleUpload}
          allowMultiple={true}
        />
      {/if}
    </div>
  </div>
</div>
