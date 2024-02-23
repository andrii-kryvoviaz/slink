<script lang="ts">
  import { goto } from '$app/navigation';
  import Icon from '@iconify/svelte';
  import { fade } from 'svelte/transition';

  import { ApiClient } from '@slink/api/Client';
  import { ReactiveState } from '@slink/api/ReactiveState';
  import type { UploadedImageResponse } from '@slink/api/Response/UploadedImageResponse';
  import { toast } from '@slink/store/toast';

  import { printErrorsAsToastMessage } from '@slink/utils/ui/printErrorsAsToastMessage';

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

  const handleChange = (event: FileEvent) => {
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
      toast.warning('Only images allowed');
      return;
    }

    uploadImage(file);
  };

  const successHandler = async (response: UploadedImageResponse) => {
    await redirectToInfo(response.id);

    toast.success('Image uploaded successfully');
  };

  const errorHandler = printErrorsAsToastMessage;

  $: $uploadedImage && successHandler($uploadedImage);
  $: $uploadError && errorHandler($uploadError);
  $: processing = $isLoading || $pageIsChanging;
</script>

<svelte:document on:paste={handleChange} />

<div
  class="content dropzone flex h-full w-full flex-col items-center justify-center p-12"
>
  <div
    in:fade={{ duration: 300 }}
    class="flex w-full flex-col items-center justify-center"
  >
    <Dropzone
      on:drop={handleChange}
      on:dragover={(event) => {
        event.preventDefault();
      }}
      on:change={handleChange}
      disabled={processing}
      defaultClass="flex flex-col justify-center items-center w-full h-64 bg-card-primary rounded-lg border-2 border-dropzone-primary border-dashed cursor-pointer hover:border-dropzone-secondary hover:bg-card-secondary max-h-[400px] max-w-[600px]"
    >
      {#if !processing}
        <div class="flex flex-col p-6 xs:w-[80%]">
          <div class="text-sm text-color-primary">
            <p class="flex items-center justify-center gap-x-[3px] p-3">
              <Icon icon="material-symbols-light:upload" class="h-10 w-10" />
              <span class="font-semibold">
                Drag & Drop
                <span class="font-normal">your image here</span>
              </span>
            </p>
          </div>

          <p class="divider">or</p>

          <p class="mb-4 mt-2">
            {#if data.os.name === 'Mac OS' || data.device.vendor === 'Apple'}
              <span class="kbd">⌘ cmd</span>
            {:else}
              <kbd class="kbd">ctrl</kbd>
            {/if}
            <span class="m-1">+</span>
            <kbd class="kbd">v</kbd>
          </p>

          <p class="text-xs text-color-secondary">
            SVG, PNG, JPG, WEBP or GIF (MAX. 5MB)
          </p>
        </div>
      {:else}
        <div class="flex flex-col items-center justify-center">
          <Icon
            icon="mingcute:loading-line"
            class="h-10 w-10 animate-spin text-color-primary"
          />
          <p class="text-sm text-color-primary">Uploading, please wait...</p>
        </div>
      {/if}
    </Dropzone>
  </div>
</div>
