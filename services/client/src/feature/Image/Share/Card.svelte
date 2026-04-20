<script lang="ts">
  import {
    FormatPicker,
    type ImageFilter,
    type ImageParams,
    ShareLinkCopy,
  } from '@slink/feature/Image';
  import * as Share from '@slink/feature/Share';
  import { Notice } from '@slink/feature/Text';
  import { Shortcut } from '@slink/ui/components';

  import Icon from '@iconify/svelte';

  import { type ShareCardImage, ShareCardState } from './CardState.svelte';

  interface Props {
    image: ShareCardImage;
    filter?: ImageFilter;
    resizeParams?: Partial<ImageParams>;
  }

  let { image, filter = 'none', resizeParams = {} }: Props = $props();

  const state = new ShareCardState({
    getImage: () => image,
    getFilter: () => filter,
    getResizeParams: () => resizeParams,
  });
</script>

<Share.Panel state={state.share} variant="card">
  {#snippet body()}
    {#if image.supportsFormatConversion}
      <div class="mb-8">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">
          Format
        </h2>
        <FormatPicker
          value={state.selectedFormat}
          originalFormat={state.originalFormat}
          isAnimated={image.isAnimated}
          on={{ change: state.setFormat }}
        />
      </div>
    {/if}

    <div class="mb-3">
      <Share.Toolbar />
    </div>

    <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
      Resize, filter, and format changes create a new share link and reset its
      options (e.g. expiration). The original image remains unchanged.
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
          <span class="text-[10px] uppercase tracking-wide opacity-60"
            >Quick</span
          >
          <Shortcut control key="C" size="xs" />
        </span>
      </span>
    </Notice>

    <ShareLinkCopy
      value={state.directLink}
      shareUrl={state.shareUrl}
      imageAlt={image.id}
      isLoading={state.isLoading}
      onBeforeCopy={state.ensurePublished}
    />
  {/snippet}
</Share.Panel>
