<script lang="ts">
  import { onMount } from 'svelte';

  import Icon from '@iconify/svelte';
  import { fade } from 'svelte/transition';

  import { bytesToSize } from '@slink/utils/bytesConverter';

  import { Tooltip } from '@slink/components/UI/Tooltip';

  type Metadata = typeof metadata;

  interface Props {
    src: string;
    alt?: string;
    metadata: {
      height: number;
      width: number;
      mimeType: string | undefined;
      size: number | undefined;
    };
    height?: number | null;
    width?: number | null;
    stretch?: boolean;
    stretchThreshold?: number;
    showMetadata?: boolean;
    showOpenInNewTab?: boolean;
    uniqueId?: any;
  }

  let {
    src,
    alt = '',
    metadata,
    height = null,
    width = null,
    stretch = false,
    stretchThreshold = 0.4,
    showMetadata = true,
    showOpenInNewTab = true,
    uniqueId = Math.random().toString(36).substring(2),
  }: Props = $props();

  let image: HTMLImageElement | undefined = $state();
  let container: HTMLDivElement | undefined = $state();

  let originalImage = $derived(src.split('?')[0]);
  let isLoaded = $state(false);

  const urlParams = new URLSearchParams(src.split('?')[1]);
  let isSquared =
    urlParams.has('width') !== urlParams.has('height') && urlParams.has('crop');

  let aspectRatio = !isSquared ? metadata.height / metadata.width : 1;
  let remHeight = Math.floor(metadata.height / 16) - 1;
  let remWidth = Math.floor(metadata.width / 16) - 1;

  if (!height && !width) {
    if (aspectRatio > 1) {
      height = Math.min(Math.max(remHeight, 14), 40);
      width = height / aspectRatio;
    } else {
      width = Math.min(Math.max(remWidth, 14), 40);
      height = width * aspectRatio;
    }
  }

  $effect.pre(() => {
    if (height && !width) {
      width = height / aspectRatio;
    }

    if (width && !height) {
      height = width * aspectRatio;
    }
  });

  const onResize = () => {
    if (!container || !image || !width) {
      return;
    }

    const remContainerWidth = Math.floor(
      (container.parentElement?.offsetWidth || container.offsetWidth) / 16,
    );

    if (remContainerWidth < width) {
      height = remContainerWidth * aspectRatio;
    } else {
      // if image is loaded recalculate the aspect ratio
      aspectRatio = image.naturalHeight / image.naturalWidth;

      width = remContainerWidth;
      height = width * aspectRatio;
    }
  };

  onMount(onResize);

  $effect(() => {
    isLoaded && onResize();
  });

  const tooSmallToStretch = (metadata: Metadata) => {
    if (!metadata) return true;

    if (!height || !width) return true;

    return (
      metadata?.height < height * 16 * stretchThreshold ||
      metadata?.width < width * 16 * stretchThreshold
    );
  };

  let isImageStretched = $derived(
    (stretch && !tooSmallToStretch(metadata)) || src.includes('.svg'),
  );
</script>

<svelte:window onresize={onResize} />

<div
  class="relative flex max-h-full max-w-full items-center justify-center overflow-hidden rounded-md border-slate-500/10 bg-white/0"
  class:border={showMetadata || showOpenInNewTab}
  style:width="{width}rem"
  style:height="{height}rem"
  bind:this={container}
>
  <img
    bind:this={image}
    {src}
    {alt}
    onload={() => {
      isLoaded = true;
    }}
    class="transition-opacity"
    class:w-full={isImageStretched}
    class:hidden={!isLoaded}
  />
  {#if isLoaded}
    {#if showOpenInNewTab}
      <a
        href={originalImage || src}
        target="_blank"
        rel="noopener noreferrer"
        class="absolute right-0 top-0 p-2 text-slate-200 hover:text-slate-100"
      >
        <Tooltip size="xs" side="left">
          {#snippet trigger()}
            <Icon icon="system-uicons:external" />
          {/snippet}
          Open in new tab
        </Tooltip>
      </a>
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
      class="absolute inset-0 flex animate-pulse items-center justify-center bg-slate-600/10 duration-1000 dark:bg-slate-800"
      out:fade
    >
      <svg
        width="48"
        height="48"
        class="text-gray-700 dark:text-gray-200"
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
