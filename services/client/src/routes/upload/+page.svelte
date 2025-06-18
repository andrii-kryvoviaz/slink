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
    <div in:fade={{ duration: 500, delay: 100 }} class="w-full max-w-2xl mx-auto">

      {#if !data.user}
        <div class="mb-8 p-6 rounded-3xl bg-white/70 dark:bg-slate-800/70 backdrop-blur-lg border border-slate-200/50 dark:border-slate-700/50 shadow-xl shadow-blue-500/5">
          <div class="flex flex-col sm:flex-row items-center gap-4 text-center sm:text-left">
            <div class="flex-shrink-0 w-12 h-12 rounded-2xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
              <Icon icon="ph:user-circle" class="h-6 w-6 text-blue-600 dark:text-blue-400" />
            </div>
            <div class="flex-1">
              <h3 class="font-medium text-slate-900 dark:text-white mb-1">Authentication Required</h3>
              <p class="text-sm text-slate-600 dark:text-slate-400 mb-4">
                Sign in to start uploading and managing your images
              </p>
              <Button
                href="/profile/login"
                variant="outline"
                size="sm"
                class="bg-blue-600 hover:bg-blue-700 text-white border-blue-600 hover:border-blue-700 shadow-lg shadow-blue-500/25 transition-all duration-200"
              >
                <Icon icon="ph:sign-in" class="h-4 w-4 mr-2" />
                Sign In
              </Button>
            </div>
          </div>
        </div>
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
            <div class="flex flex-col items-center justify-center h-full p-10 text-center">
              <div class="mb-8 relative">
                <div class="w-20 h-20 rounded-3xl bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center shadow-lg shadow-blue-500/30 group-hover:shadow-blue-500/40 transition-all duration-300 group-hover:scale-105">
                  <Icon icon="ph:cloud-arrow-up" class="h-10 w-10 text-white" />
                </div>
                <div class="absolute -top-1 -right-1 w-6 h-6 bg-green-500 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                  <Icon icon="ph:plus" class="h-3 w-3 text-white" />
                </div>
              </div>

              <div class="mb-8 max-w-sm">
                <h3 class="text-2xl font-light text-slate-900 dark:text-white mb-3">
                  Drop your image
                </h3>
                <p class="text-slate-500 dark:text-slate-400">
                  Drag & drop your image here, or click to browse
                </p>
              </div>

              <div class="flex items-center gap-3 mb-8 px-4 py-2 rounded-full bg-slate-100 dark:bg-slate-700/50">
                <span class="text-sm text-slate-600 dark:text-slate-400">Quick paste:</span>
                <Shourtcut control={true} key="v" size="sm" />
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
                <div class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center animate-pulse">
                  <Icon icon="ph:cloud-arrow-up" class="h-8 w-8 text-white" />
                </div>
                <div class="absolute inset-0 w-16 h-16 rounded-full border-4 border-transparent border-t-blue-500 animate-spin"></div>
              </div>
              <h3 class="text-xl font-light text-slate-700 dark:text-slate-300 mb-2">
                Uploading...
              </h3>
              <p class="text-sm text-slate-500 dark:text-slate-400">
                Your image is being processed
              </p>
            </div>
          {/if}
        </Dropzone>

        {#if processing}
          <div class="absolute inset-0 bg-white/90 dark:bg-slate-800/90 backdrop-blur-md rounded-3xl flex items-center justify-center">
            <div class="text-center">
              <div class="relative inline-flex items-center justify-center w-16 h-16 mb-4">
                <div class="absolute inset-0 w-16 h-16 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 animate-pulse"></div>
                <div class="absolute inset-2 w-12 h-12 rounded-full bg-white dark:bg-slate-800 flex items-center justify-center">
                  <div class="w-6 h-6 border-2 border-transparent border-t-blue-500 rounded-full animate-spin"></div>
                </div>
              </div>
              <p class="text-lg font-light text-slate-700 dark:text-slate-300 mb-1">Processing</p>
              <p class="text-sm text-slate-500 dark:text-slate-400">Almost done...</p>
            </div>
          </div>
        {/if}
      </div>
    </div>
  </div>
</div>
