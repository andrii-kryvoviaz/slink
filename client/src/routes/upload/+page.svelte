<script lang="ts">
  import { goto } from '$app/navigation';
  import Icon from '@iconify/svelte';
  import { fade } from 'svelte/transition';

  import { ApiClient } from '@slink/api/Client';
  import { ReactiveState } from '@slink/api/ReactiveState';
  import type { UploadedImageResponse } from '@slink/api/Response';

  import { printErrorsAsToastMessage } from '@slink/utils/ui/printErrorsAsToastMessage';
  import { toast } from '@slink/utils/ui/toast';

  import { Button, Loader } from '@slink/components/Common';
  import { Dropzone } from '@slink/components/Form';

  import type { PageData } from './$types';

  export let data: PageData;

  let processing: boolean = false;

  type FileEvent = DragEvent | ClipboardEvent | Event;

  const {
    isLoading,
    data: uploadedImage,
    error: uploadError,
    run: uploadImage,
  } = ReactiveState<UploadedImageResponse>((file: File) =>
    ApiClient.image.upload(file)
  );

  const { isLoading: pageIsChanging, run: redirectToInfo } = ReactiveState(
    (imageId: number) => goto(`/info/${imageId}`)
  );

  const getFilesFromEvent = function (
    event: FileEvent
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
      toast.warning(`Unsupported file format.
        <a href="/help/faq#supported-image-formats"
            class="text-indigo-500 hover:text-indigo-700 mt-1 block"
        >See supported formats</a>
      `);
      return;
    }

    await uploadImage(file);

    (event.target as HTMLInputElement).value = '';
  };

  const successHandler = async (response: UploadedImageResponse) => {
    await redirectToInfo(response.id);
  };

  const errorHandler = printErrorsAsToastMessage;

  $: $uploadedImage && successHandler($uploadedImage);
  $: $uploadError && errorHandler($uploadError);
  $: processing = $isLoading || $pageIsChanging;
  $: disabled = processing || !data.user;
</script>

<svelte:head>
  <title>Upload | Slink</title>
</svelte:head>

<svelte:document on:paste={handleChange} />

<div
  class="content dropzone flex h-full w-full flex-col items-center p-4 sm:p-12"
>
  <div
    in:fade={{ duration: 300 }}
    class="flex w-full flex-col items-center justify-center"
  >
    <div class="flex w-full max-w-[600px] flex-col gap-6">
      {#if !data.user}
        <div
          role="alert"
          class="alert rounded-lg border-gray-400/20 bg-indigo-600/70 text-white dark:text-gray-200"
        >
          <Icon
            icon="material-symbols-light:warning-outline"
            class="hidden h-6 w-6 text-gray-300 dark:text-gray-400 md:block"
          />
          <span class="text-sm">
            You must be logged in to be able to upload images. Anonymous uploads
            are not allowed.
          </span>
          <div class="w-full sm:w-auto">
            <Button
              size="sm"
              variant="dark"
              href="/profile/login"
              class="w-full"
            >
              <span>Log in</span>
            </Button>
          </div>
        </div>
      {/if}
      <Dropzone
        on:drop={handleChange}
        on:dragover={(event) => {
          event.preventDefault();
        }}
        on:change={handleChange}
        {disabled}
        defaultClass="flex flex-col justify-center items-center w-full h-64 bg-card-primary rounded-lg border-2 border-dropzone-primary border-dashed cursor-pointer hover:border-dropzone-secondary hover:bg-card-secondary max-h-[400px] "
      >
        {#if !processing}
          <div class="flex flex-col p-6 xs:w-[80%]">
            <div class="text-sm text-color-primary">
              <p class="flex items-center justify-center gap-x-[3px] p-3">
                <Icon icon="material-symbols-light:upload" class="h-10 w-10" />
                <span class="hidden font-semibold sm:block">
                  Drag & Drop
                  <span class="font-normal">your image here</span>
                </span>
                <span class="block font-semibold sm:hidden">Upload Image</span>
              </p>
            </div>

            <p class="divider hidden sm:flex">or</p>

            <p class="mb-4 mt-2 hidden sm:block">
              {#if data.os?.name === 'Mac OS' || data.device?.vendor === 'Apple'}
                <span class="kbd">⌘ cmd</span>
              {:else}
                <kbd class="kbd">ctrl</kbd>
              {/if}
              <span class="m-1">+</span>
              <kbd class="kbd">v</kbd>
            </p>

            <p class="text-xs text-color-secondary">
              SVG, PNG, JPG, WEBP, BMP, GIF or ICO
            </p>
            <a
              href="/help/faq#supported-image-formats"
              class="mt-1 block text-[0.75em] text-gray-600 hover:text-color-primary"
              on:click={(event) => event.stopPropagation()}
            >
              See all supported formats
            </a>
          </div>
        {:else}
          <div class="flex flex-col items-center justify-center">
            <Loader>
              <p
                class="text-md font-extralight tracking-wide text-color-primary"
              >
                Uploading, please wait...
              </p>
            </Loader>
          </div>
        {/if}
      </Dropzone>
    </div>
  </div>
</div>
