<script lang="ts">
  import type { PageData } from './$types';
  import type { UploadedImageResponse } from '@slink/api/Response';

  import { goto } from '$app/navigation';
  import Icon from '@iconify/svelte';
  import { fade } from 'svelte/transition';

  import { ApiClient } from '@slink/api/Client';
  import { ReactiveState } from '@slink/api/ReactiveState';

  import { useUploadHistoryFeed } from '@slink/lib/state/UploadHistoryFeed.svelte';

  import { printErrorsAsToastMessage } from '@slink/utils/ui/printErrorsAsToastMessage';
  import { toast } from '@slink/utils/ui/toast';

  import UnsupportedFileFormat from '@slink/components/Feature/Image/UnsupportedFIleFormat/UnsupportedFileFormat.svelte';
  import { Button } from '@slink/components/UI/Action';
  import { Shourtcut } from '@slink/components/UI/Action';
  import {
    Banner,
    BannerAction,
    BannerContent,
    BannerIcon,
  } from '@slink/components/UI/Banner';
  import { Dropzone } from '@slink/components/UI/Form';
  import { Loader } from '@slink/components/UI/Loader';

  interface Props {
    data: PageData;
  }

  let { data }: Props = $props();
  type FileEvent = DragEvent | ClipboardEvent | Event;

  const {
    isLoading,
    data: uploadedImage,
    error: uploadError,
    run: uploadImage,
  } = ReactiveState<UploadedImageResponse>((file: File) =>
    ApiClient.image.upload(file),
  );

  const { isLoading: pageIsChanging, run: redirectToInfo } = ReactiveState(
    (imageId: number) => goto(`/info/${imageId}`),
  );

  const getFilesFromEvent = function (
    event: FileEvent,
  ): FileList | undefined | null {
    if (event instanceof DragEvent) {
      return event.dataTransfer?.files;
    } else if (event instanceof ClipboardEvent) {
      return event.clipboardData?.files;
    }

    return (event.target as HTMLInputElement).files;
  };

  const handleChange = async (event: FileEvent) => {
    if (disabled) return;

    event.preventDefault();

    const fileList = getFilesFromEvent(event);

    if (!fileList) {
      toast.warning('No files selected');
      return;
    }

    if (fileList.length > 1) {
      toast.warning('Only one file allowed at a time');
      return;
    }

    const file = fileList.item(0) as File;

    if (!file?.type.startsWith('image/')) {
      toast.component(UnsupportedFileFormat, { duration: 5000 });
      return;
    }

    await uploadImage(file);

    (event.target as HTMLInputElement).value = '';
  };

  const successHandler = async (response: UploadedImageResponse) => {
    const historyFeedState = useUploadHistoryFeed();

    await redirectToInfo(response.id);

    const images = await ApiClient.image.getImagesByIds([response.id]);
    images.data.forEach((image) => historyFeedState.add(image));
  };

  const errorHandler = printErrorsAsToastMessage;

  $effect(() => {
    $uploadedImage && successHandler($uploadedImage);
  });

  $effect(() => {
    $uploadError && errorHandler($uploadError);
  });

  let processing = $derived($isLoading || $pageIsChanging);
  let disabled = $derived(processing || !data.user);
</script>

<svelte:head>
  <title>Upload | Slink</title>
</svelte:head>

<svelte:document onpaste={handleChange} />

<div class="min-h-full">
  <div class="container mx-auto p-6 pt-16">
    <div
      in:fade={{ duration: 500, delay: 100 }}
      class="w-full max-w-2xl mx-auto"
    >
      {#if !data.user}
        <Banner variant="warning">
          {#snippet icon()}
            <BannerIcon variant="warning" icon="ph:lock-simple" />
          {/snippet}
          {#snippet content()}
            <BannerContent
              title="Sign in to continue"
              description="Upload and manage your images"
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

      <div class="relative">
        <Dropzone
          ondrop={handleChange}
          ondragover={(event) => event.preventDefault()}
          onchange={handleChange}
          {disabled}
          class="group relative w-full h-96 bg-white/80 dark:bg-slate-800/80 rounded-3xl border-2 border-dashed border-slate-200 dark:border-slate-700 hover:border-slate-400 dark:hover:border-slate-500 hover:bg-white dark:hover:bg-slate-800/90 transition-all duration-300 cursor-pointer backdrop-blur-xl shadow-2xl shadow-slate-500/10 dark:shadow-black/20"
        >
          {#if !processing}
            <div
              class="flex flex-col items-center justify-center h-full p-10 text-center"
            >
              <div class="mb-8 relative">
                <div
                  class="w-20 h-20 rounded-3xl bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center shadow-lg shadow-blue-500/30 group-hover:shadow-blue-500/40 transition-all duration-300 group-hover:scale-105"
                >
                  <Icon icon="ph:cloud-arrow-up" class="h-10 w-10 text-white" />
                </div>
              </div>

              <div class="mb-8 max-w-sm">
                <p class="text-slate-500 dark:text-slate-400">
                  Drag & drop your image here, or click to browse
                </p>
              </div>

              <div
                class="flex items-center gap-3 mb-8 px-4 py-2 rounded-full bg-slate-100 dark:bg-slate-700/50"
              >
                <span class="text-sm text-slate-600 dark:text-slate-400"
                  >Quick paste:</span
                >
                <Shourtcut control={true} key="v" size="lg" />
              </div>

              <div class="text-center">
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-2">
                  PNG • JPG • GIF • SVG • WebP • HEIC
                </p>
                <a
                  href="/help/faq#supported-image-formats"
                  class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition-colors duration-200 underline underline-offset-2"
                  onclick={(event) => event.stopPropagation()}
                >
                  View all supported formats →
                </a>
              </div>
            </div>
          {:else}
            <div class="flex flex-col items-center justify-center h-full">
              <div class="relative mb-6">
                <div
                  class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center animate-pulse"
                >
                  <Icon icon="ph:cloud-arrow-up" class="h-8 w-8 text-white" />
                </div>
                <div class="absolute inset-0 flex items-center justify-center">
                  <Loader variant="subtle" size="lg" />
                </div>
              </div>
              <h3
                class="text-xl font-light text-slate-700 dark:text-slate-300 mb-2"
              >
                Uploading...
              </h3>
              <p class="text-sm text-slate-500 dark:text-slate-400">
                Your image is being processed
              </p>
            </div>
          {/if}
        </Dropzone>

        {#if processing}
          <div
            class="absolute inset-0 bg-white/90 dark:bg-slate-800/90 backdrop-blur-md rounded-3xl flex items-center justify-center"
          >
            <div class="text-center">
              <div class="mb-4">
                <Loader variant="modern" size="xl" />
              </div>
              <p
                class="text-lg font-light text-slate-700 dark:text-slate-300 mb-1"
              >
                Processing
              </p>
              <p class="text-sm text-slate-500 dark:text-slate-400">
                Almost done...
              </p>
            </div>
          </div>
        {/if}
      </div>
    </div>
  </div>
</div>
