<script lang="ts">
  import { fade } from 'svelte/transition';
  import { bytesToSize } from '../../../utils/bytesConverter';
  import Icon from '@iconify/svelte';
  import { Tooltip } from 'flowbite-svelte';

  export let src: string;
  export let alt: string = '';

  export let metadata: {
    height: number;
    width: number;
    mimeType: string;
    size: number;
  } | null = null;
  export let aspectRatio: number = 0;
  export let height = 24;

  let width = 0;
  let isLoaded = false;

  let maxWidth = 0;
  let maxHeight = 0;

  let image: HTMLImageElement;
  let container: HTMLDivElement;

  $: if (aspectRatio) {
    width = height / aspectRatio;
  }

  $: if (isLoaded && !aspectRatio) {
    aspectRatio = image.naturalHeight / image.naturalWidth;
  }

  $: if (isLoaded) {
    // Reset the heigt to auto so that the image can resize freely
    container.style.height = `auto`;
  }

  $: if (metadata) {
    maxWidth = metadata.width;
    maxHeight = metadata.height;
  }
</script>

<div
  class="rounded-m relative w-full overflow-hidden"
  style:width="{width}rem"
  style:height="{height}rem"
  style:max-width="min({maxWidth}px, 100%)"
  style:max-height="min({maxHeight}px, {height}rem)"
  bind:this={container}
>
  <img
    bind:this={image}
    {src}
    {alt}
    on:load={() => {
      isLoaded = true;
    }}
    class="h-full w-full object-contain transition-opacity"
    class:hidden={!isLoaded}
  />
  {#if isLoaded}
    <a
      href={src}
      target="_blank"
      rel="noopener noreferrer"
      class="absolute right-0 top-0 p-2 text-slate-200 hover:text-slate-100"
      id="open-tooltip"
    >
      <Icon icon="system-uicons:external" />
    </a>

    <Tooltip
      triggeredBy="[id^='open-tooltip']"
      class="p-2 text-xs"
      color="dark"
    >
      Open in new tab</Tooltip
    >

    {#if metadata}
      <div
        class="absolute bottom-0 left-0 right-0 flex items-center justify-between bg-slate-800 bg-opacity-50 p-2 text-xs text-slate-200 backdrop-blur-sm backdrop-filter"
      >
        <div class="group">
          <p>{metadata.width}x{metadata.height} pixels</p>
          <p>{metadata.mimeType}</p>
        </div>

        <p>{bytesToSize(metadata.size)}</p>
      </div>
    {/if}
  {/if}
  {#if !isLoaded}
    <div
      class="absolute inset-0 flex animate-pulse items-center justify-center bg-slate-800 duration-1000"
      out:fade
    >
      <svg
        width="48"
        height="48"
        class="text-gray-200"
        xmlns="http://www.w3.org/2000/svg"
        aria-hidden="true"
        fill="currentColor"
        viewBox="0 0 640 512"
      >
        <path
          d="M480 80C480 35.82 515.8 0 560 0C604.2 0 640 35.82 640 80C640 124.2 604.2 160 560 160C515.8 160 480 124.2 480 80zM0 456.1C0 445.6 2.964 435.3 8.551 426.4L225.3 81.01C231.9 70.42 243.5 64 256 64C268.5 64 280.1 70.42 286.8 81.01L412.7 281.7L460.9 202.7C464.1 196.1 472.2 192 480 192C487.8 192 495 196.1 499.1 202.7L631.1 419.1C636.9 428.6 640 439.7 640 450.9C640 484.6 612.6 512 578.9 512H55.91C25.03 512 .0006 486.1 .0006 456.1L0 456.1z"
        />
      </svg>
    </div>
  {/if}
</div>