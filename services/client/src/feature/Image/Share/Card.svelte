<script lang="ts">
  import {
    FilterChip,
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
    previewUrl?: string;
    onFilterChange?: (filter: ImageFilter) => void;
    resizeParams?: Partial<ImageParams>;
    onPublished?: (shareId: string) => void | Promise<void>;
  }

  let {
    image,
    filter = 'none',
    previewUrl,
    onFilterChange,
    resizeParams = {},
    onPublished,
  }: Props = $props();

  const state = new ShareCardState({
    getImage: () => image,
    getFilter: () => filter,
    getResizeParams: () => resizeParams,
    onPublished: (shareId) => onPublished?.(shareId),
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

    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">
      Share
    </h2>

    <div class="mb-4">
      <Share.AttributeChips>
        {#snippet prepend()}
          {#if previewUrl && onFilterChange}
            <FilterChip
              {previewUrl}
              value={filter}
              on={{ change: onFilterChange }}
            />
          {/if}
        {/snippet}
      </Share.AttributeChips>
    </div>

    <ShareLinkCopy
      value={state.directLink}
      shareUrl={state.shareUrl}
      imageAlt={image.id}
      isLoading={state.isLoading}
      onBeforeCopy={state.ensurePublished}
    />

    <Notice variant="info" size="xs" class="mt-4">
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
  {/snippet}
</Share.Panel>
