<script lang="ts">
  import Icon from '@iconify/svelte';
  import { fade } from 'svelte/transition';

  import { bytesToSize } from '@slink/utils/bytesConverter';

  import { Tooltip } from '@slink/components/Common';

  export let src: string;
  export let alt: string = '';

  export let metadata: {
    height: number;
    width: number;
    mimeType: string | undefined;
    size: number | undefined;
  } | null = null;

  export let aspectRatio: number = 0;
  export let height: number | null = null;
  export let width: number | null = null;
  export let stretch: boolean = false;
  export let showMetadata = true;
  export let showOpenInNewTab = true;

  export let uniqueId = Math.random().toString(36).substring(2);

  let originalImage: string | undefined;
  let isLoaded = false;

  let image: HTMLImageElement;
  let container: HTMLDivElement;

  $: if (isLoaded && !aspectRatio) {
    aspectRatio = image.naturalHeight / image.naturalWidth;
  }

  $: if (isLoaded && height) {
    // Reset the height to auto so that the image can resize freely
    const remImageHeight = Math.floor(image.naturalHeight / 16) - 1;
    if (remImageHeight > height) {
      container.style.height = `auto`;
    }
  }

  $: if (metadata) {
    aspectRatio = metadata.height / metadata.width;

    if (height && !width) {
      width = height / aspectRatio;
    }

    if (width && !height) {
      height = width * aspectRatio;
    }
  }

  $: if (metadata && !height && !width) {
    const remHeight = Math.floor(metadata.height / 16) - 1;

    height = Math.min(Math.max(remHeight, 14), 30);
    width = Math.floor(height / aspectRatio);
  }

  $: if (src && src !== originalImage) {
    originalImage = src.split('?')[0];
  }

  // fallback for when the image is already loaded and the on:load event is not triggered
  $: if (image && image.complete) {
    isLoaded = true;
  }

  const onResize = () => {
    if (container && width && stretch) {
      const remContainerWidth = Math.floor(container.offsetWidth / 16);

      if (remContainerWidth < width) {
        height = remContainerWidth * aspectRatio;
      } else {
        height = width * aspectRatio;
      }
    }
  };
</script>

<svelte:window on:resize={onResize} />

<div
  class="relative flex max-h-full max-w-full items-center justify-center overflow-hidden rounded-md border-slate-500/10"
  class:border={showMetadata || showOpenInNewTab}
  style:width="{width}rem"
  style:height="{height}rem"
  bind:this={container}
>
  <img
    bind:this={image}
    {src}
    {alt}
    on:load={() => {
      isLoaded = true;
    }}
    class="transition-opacity"
    class:w-full={stretch}
    class:h-full={stretch}
    class:hidden={!isLoaded}
  />
  {#if isLoaded}
    {#if showOpenInNewTab}
      <a
        href={originalImage || src}
        target="_blank"
        rel="noopener noreferrer"
        class="absolute right-0 top-0 p-2 text-slate-200 hover:text-slate-100"
        id={`open-tooltip-${uniqueId}`}
      >
        <Icon icon="system-uicons:external" />
      </a>

      <Tooltip
        triggeredBy={`[id^='open-tooltip-${uniqueId}']`}
        class="min-w-[7rem] p-2 text-xs"
        color="dark"
      >
        Open in new tab</Tooltip
      >
    {/if}

    {#if metadata && showMetadata}
      <div
        class="absolute bottom-0 left-0 right-0 flex items-center justify-between bg-slate-800 bg-opacity-50 p-2 text-xs text-slate-200 backdrop-blur-sm backdrop-filter"
      >
        <div class="group">
          <p>{metadata.width}x{metadata.height} pixels</p>
          {#if metadata.mimeType}
            <p>{metadata.mimeType}</p>
          {/if}
        </div>

        {#if metadata.size}
          <p>{bytesToSize(metadata.size)}</p>
        {/if}
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
