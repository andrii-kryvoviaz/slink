<script lang="ts">
  import { Dropzone } from 'flowbite-svelte';
  import Icon from '@iconify/svelte';
  import { ApiClient } from '../api/Client';
  import { goto } from '$app/navigation';
  import { toast } from '../store/toast';
  import { ValidationException } from '../api/Exceptions/ValidationException';

  let isLoading: boolean = false;
  let isLogged: boolean = true;

  type FileEvent = DragEvent | ClipboardEvent | Event;

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

    handleSend(file);
  };

  const handleSend = async (file: File) => {
    if (isLoading) return;

    isLoading = true;

    try {
      const response = await ApiClient.image.upload(file);
      await goto(`/image/${response.id}`);

      toast.success('Image uploaded successfully');
    } catch (error: any) {
      if (error instanceof ValidationException) {
        error.violations.forEach((violation) => {
          toast.error(violation.message);
        });
      } else {
        toast.error('Something went wrong');
      }
    } finally {
      isLoading = false;
    }
  };
</script>

<svelte:document on:paste={handleChange} />

<div
  class="content dropzone flex h-full w-full flex-col items-center justify-center p-12"
>
  <Dropzone
    on:drop={handleChange}
    on:dragover={(event) => {
      event.preventDefault();
    }}
    on:change={handleChange}
    disabled={isLoading}
    defaultClass="flex flex-col justify-center items-center w-full h-64 bg-card-primary rounded-lg border-2 border-primary border-dashed cursor-pointer hover:border-secondary hover:bg-card-secondary max-h-[400px] max-w-[600px]"
  >
    {#if !isLoading}
      <div class="flex flex-col">
        <div class=" text-sm text-color-primary">
          <p class="flex items-center gap-x-[3px]">
            <Icon icon="material-symbols-light:upload" class="h-10 w-10" />
            <span class="font-semibold">Drag & Drop</span> or
            <span class="font-semibold">paste</span> your image here
          </p>
        </div>

        <p class="text-xs text-color-secondary">
          SVG, PNG, JPG, WEBP or GIF (MAX. 3MB)
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
  <p class="p-4 text-sm text-color-secondary">
    {#if isLogged}
      <a href="/login" class="text-color-accent">Login</a> to see your images
    {:else}
      <a href="/explore" class="text-color-accent">Explore all images</a>
    {/if}
  </p>
</div>
