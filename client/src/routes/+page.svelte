<script lang="ts">
  import { Dropzone } from 'flowbite-svelte';
  import Icon from '@iconify/svelte';
  import { ApiClient } from '../api/Client';

  let files: File[] = [];

  const dropHandle = (event: any) => {
    files = [];
    event.preventDefault();

    const items = event.dataTransfer.items || event.dataTransfer.files;

    Array.from(items).forEach((item: typeof items) => {
      if (item.kind === 'file' && item.type.indexOf('image') !== -1) {
        files.push(item.getAsFile());
      }
    });

    handleSend();
  };

  const handleChange = (event: any) => {
    if (event.target.files.length > 0) {
      files = [...event.target.files];
    }
    handleSend();
  };

  const handlePaste = async (event: ClipboardEvent) => {
    const items = event.clipboardData?.items;
    if (!items) return;

    const imageFiles = Array.from(items)
      .filter((item) => item.type.indexOf('image') !== -1)
      .map((item) => item.getAsFile())
      .filter((file) => file !== null) as File[];

    files = imageFiles;

    handleSend();
  };

  const handleSend = async () => {
    if (files.length === 0) return;

    await ApiClient.image.upload(files);
  };
</script>

<svelte:document on:paste={handlePaste} />

<div class="content dropzone flex h-full w-full items-center p-12 align-middle">
  <Dropzone
    on:drop={dropHandle}
    on:dragover={(event) => {
      event.preventDefault();
    }}
    on:change={handleChange}
  >
    <div class="flex max-h-[400px] max-w-md cursor-pointer flex-col">
      <p
        class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-200"
      >
        <Icon
          icon="material-symbols-light:upload"
          class="h-10 w-10 text-gray-400"
        />
        <span class="font-semibold">Click to upload</span> or drag and drop
      </p>
      <p class="text-xs text-gray-500 dark:text-gray-400">
        SVG, PNG, JPG, WEBP or GIF (MAX. 3MB)
      </p>
    </div>
    {#each files as file}
      <p class="mb-2 text-sm text-gray-500 dark:text-gray-200">
        {file.name}
      </p>
    {/each}
  </Dropzone>
</div>
