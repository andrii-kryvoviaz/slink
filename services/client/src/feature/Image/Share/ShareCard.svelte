<script lang="ts">
  import {
    FormatPicker,
    type ImageFilter,
    type ImageParams,
    ShareLinkCopy,
  } from '@slink/feature/Image';
  import { Notice } from '@slink/feature/Text';
  import { Shortcut } from '@slink/ui/components';

  import Icon from '@iconify/svelte';

  import {
    type ShareCardImage,
    createShareCardState,
  } from './ShareCardState.svelte';

  interface Props {
    image: ShareCardImage;
    filter?: ImageFilter;
    resizeParams?: Partial<ImageParams>;
  }

  let { image, filter = 'none', resizeParams = {} }: Props = $props();

  const state = createShareCardState({
    getImage: () => image,
    getFilter: () => filter,
    getResizeParams: () => resizeParams,
  });
</script>

<div>
  <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">
    Share
  </h2>
  <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
    Resize, filter, and format changes apply only to the shared link. The
    original image remains unchanged.
  </p>
  <Notice variant="info" size="xs" class="mb-4">
    <span class="flex items-center justify-between">
      <span class="flex items-center gap-2">
        <Icon icon="lucide:clipboard-copy" class="h-3.5 w-3.5 shrink-0" />
        <span>Select option to copy</span>
      </span>
      <span
        class="flex items-center gap-1.5 pl-3 border-l border-violet-300 dark:border-violet-600"
      >
        <span class="text-[10px] uppercase tracking-wide opacity-60">Quick</span
        >
        <Shortcut control key="C" size="xs" />
      </span>
    </span>
  </Notice>

  {#if image.supportsFormatConversion}
    <div class="mb-4">
      <span
        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
      >
        Output Format
      </span>
      <FormatPicker
        value={state.selectedFormat}
        originalFormat={state.originalFormat}
        isAnimated={image.isAnimated}
        on={{ change: state.handleFormatChange }}
      />
    </div>
  {/if}

  <ShareLinkCopy
    value={state.directLink}
    shareUrl={state.shareUrl}
    imageAlt={image.id}
    isLoading={state.isLoading}
    onBeforeCopy={state.handleBeforeCopy}
  />
</div>
