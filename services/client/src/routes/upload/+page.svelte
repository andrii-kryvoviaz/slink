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
        ondrop={handleChange}
        ondragover={(event) => {
          event.preventDefault();
        }}
        onchange={handleChange}
        {disabled}
      >
        {#if !processing}
          <div class="flex flex-col p-6 xs:w-[80%]">
            <div class="text-sm text-text-primary">
              <p class="flex items-center justify-center gap-x-[3px] p-3">
                <Icon icon="material-symbols-light:upload" class="h-10 w-10" />
                <span class="hidden font-semibold sm:block">
                  Drag & Drop
                  <span class="font-normal">your image here</span>
                </span>
                <span class="block font-semibold sm:hidden">Upload Image</span>
              </p>
            </div>

            <p
              class="divider hidden sm:flex before:bg-bc-delimiter/40 after:bg-bc-delimiter/40"
            >
              or
            </p>

            <div class="mb-4 mt-2 hidden sm:block">
              <Shourtcut control={true} key="v" size="lg" />
            </div>

            <p class="text-xs text-text-secondary">
              SVG, PNG, JPG, BMP, GIF or HEIC
            </p>
            <a
              href="/help/faq#supported-image-formats"
              class="mt-1 block text-[0.75em] text-gray-600 hover:text-text-primary"
              onclick={(event) => event.stopPropagation()}
            >
              See all supported formats
            </a>
          </div>
        {:else}
          <div class="flex flex-col items-center justify-center">
            <Loader>
              <p
                class="text-md font-extralight tracking-wide text-text-primary"
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
