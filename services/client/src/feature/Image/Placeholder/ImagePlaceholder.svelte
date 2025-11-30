<script lang="ts">
  import { Tooltip, TooltipProvider } from '@slink/ui/components/tooltip';
  import { AspectRatio } from 'bits-ui';

  import { bytesToSize } from '$lib/utils/bytesConverter';
  import { className as cn } from '$lib/utils/ui/className';
  import Icon from '@iconify/svelte';
  import { fade } from 'svelte/transition';

  interface Props {
    src: string;
    alt?: string;
    metadata: {
      height: number;
      width: number;
      mimeType: string | undefined;
      size: number | undefined;
    };
    class?: string;
    stretch?: boolean;
    stretchThreshold?: number;
    showMetadata?: boolean;
    showOpenInNewTab?: boolean;
    uniqueId?: any;
    rounded?: boolean;
    keepAspectRatio?: boolean;
  }

  let {
    src,
    alt = '',
    metadata,
    class: className = '',
    stretch = false,
    stretchThreshold = 0.4,
    showMetadata = true,
    showOpenInNewTab = true,
    rounded = true,
    keepAspectRatio = true,
  }: Props = $props();

  let isLoaded = $state(false);
  let originalImage = $derived(src.split('?')[0]);
  let actualAspectRatio = $state(metadata.width / metadata.height);

  let isSvg = $derived(
    src.includes('.svg') || metadata.mimeType === 'image/svg+xml',
  );

  let aspectRatio = $derived(() => {
    if (
      isSvg &&
      (metadata.height === 0 ||
        metadata.width === 0 ||
        !isFinite(actualAspectRatio))
    ) {
      return 1;
    }
    return actualAspectRatio;
  });

  let shouldUseAspectRatio = $derived(
    keepAspectRatio &&
      !(isSvg && (metadata.height === 0 || metadata.width === 0)),
  );

  let finalRatio = $derived(shouldUseAspectRatio ? aspectRatio() : 1);

  const urlHasSizingParams = (url: string): boolean => {
    const params = new URLSearchParams(url.split('?')[1] || '');
    if (params.has('width') || params.has('height')) {
      return true;
    }

    if (url.includes('crop')) {
      return true;
    }

    return false;
  };

  const updateAspectRatioFromImage = (img: HTMLImageElement) => {
    if (urlHasSizingParams(src)) {
      const naturalWidth = img.naturalWidth || metadata.width;
      const naturalHeight = img.naturalHeight || metadata.height;

      if (naturalWidth > 0 && naturalHeight > 0) {
        actualAspectRatio = naturalWidth / naturalHeight;
      }
    }
  };

  let shouldStretch = $derived(() => {
    if (isSvg) return true;
    if (!stretch) return false;

    const minRequiredHeight = metadata.height * stretchThreshold;
    const minRequiredWidth = metadata.width * stretchThreshold;

    return (
      metadata.height >= minRequiredHeight && metadata.width >= minRequiredWidth
    );
  });
</script>

<TooltipProvider delayDuration={300}>
  <AspectRatio.Root
    ratio={finalRatio}
    class={cn(
      'group relative flex items-center justify-center overflow-hidden border-slate-500/10 bg-white/0 w-full',
      className,
      rounded && 'rounded-md',
      (showMetadata || showOpenInNewTab) && 'border',
    )}
  >
    <img
      {src}
      {alt}
      onload={(event) => {
        const img = event.target as HTMLImageElement;
        updateAspectRatioFromImage(img);
        isLoaded = true;
      }}
      onerror={() => {
        isLoaded = true;
      }}
      class={cn(
        'transition-opacity border-none',
        (shouldStretch() || keepAspectRatio) && 'w-full h-full',
        keepAspectRatio && !isSvg && 'object-contain',
        !keepAspectRatio && !isSvg && 'object-fill',
        isSvg && 'w-full h-full object-contain',
        !isLoaded && !isSvg && 'hidden',
      )}
    />

    {#if !isLoaded && !isSvg}
      <div
        class="absolute inset-0 flex items-center justify-center bg-gray-100 dark:bg-gray-800 animate-pulse"
      >
        <svg
          width="48"
          height="48"
          class="text-gray-400"
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

    {#if isLoaded}
      {#if showOpenInNewTab}
        <a
          href={originalImage || src}
          target="_blank"
          rel="noopener noreferrer"
          onclick={(e) => e.stopPropagation()}
          class="group/link absolute right-2 top-2 flex h-8 w-8 items-center justify-center rounded-lg bg-black/20 backdrop-blur-sm opacity-0 transition-all duration-200 group-hover:opacity-100 hover:bg-black/40 hover:scale-110"
        >
          <Tooltip size="xs" side="left" sideOffset={12}>
            {#snippet trigger()}
              <Icon
                icon="heroicons:arrow-top-right-on-square"
                class="h-4 w-4 text-white/80 transition-colors duration-200 group-hover/link:text-white"
              />
            {/snippet}
            Open in new tab
          </Tooltip>
        </a>
      {/if}

      {#if metadata && showMetadata}
        <div
          class="absolute bottom-3 left-3 right-3 flex flex-wrap items-center justify-between gap-2 bg-black/40 dark:bg-black/60 backdrop-blur-md rounded-lg px-3 py-2 text-xs text-white/90"
          in:fade={{ duration: 200, delay: 100 }}
        >
          <div class="flex flex-wrap items-center gap-2 min-w-0">
            <div class="flex items-center gap-1 whitespace-nowrap">
              <Icon
                icon="heroicons:photo"
                class="w-3 h-3 text-white/70 flex-shrink-0"
              />
              <span class="font-medium">{metadata.width}×{metadata.height}</span
              >
            </div>
            {#if metadata.mimeType}
              <div class="flex items-center gap-1 whitespace-nowrap">
                <Icon
                  icon="heroicons:document"
                  class="w-3 h-3 text-white/70 flex-shrink-0"
                />
                <span class="uppercase font-medium"
                  >{metadata.mimeType.split('/')[1] || metadata.mimeType}</span
                >
              </div>
            {/if}
          </div>

          {#if metadata.size}
            <div class="flex items-center gap-1 whitespace-nowrap">
              <Icon
                icon="heroicons:arrow-down-tray"
                class="w-3 h-3 text-white/70 flex-shrink-0"
              />
              <span class="font-medium">{bytesToSize(metadata.size)}</span>
            </div>
          {/if}
        </div>
      {/if}
    {/if}
  </AspectRatio.Root>
</TooltipProvider>
