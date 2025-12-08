<script lang="ts">
  import { Tooltip, type TooltipVariant } from '@slink/ui/components/tooltip';

  import { downloadByLink } from '$lib/utils/http/downloadByLink';
  import Icon from '@iconify/svelte';

  import {
    type DownloadButtonSize,
    type DownloadButtonVariant,
    downloadButtonTheme,
    downloadIconTheme,
  } from './DownloadButton.theme';

  interface Props {
    imageUrl: string;
    fileName: string;
    size?: DownloadButtonSize;
    variant?: DownloadButtonVariant;
    tooltipVariant?: TooltipVariant;
  }

  let {
    imageUrl,
    fileName,
    size = 'md',
    variant = 'default',
    tooltipVariant = 'subtle',
  }: Props = $props();

  let isLoading = $state(false);

  const handleClick = async (e: MouseEvent) => {
    e.stopPropagation();
    e.preventDefault();

    if (isLoading) return;

    isLoading = true;

    try {
      downloadByLink(imageUrl, fileName);
    } finally {
      setTimeout(() => {
        isLoading = false;
      }, 500);
    }
  };
</script>

<Tooltip side="top" sideOffset={6} variant={tooltipVariant}>
  {#snippet trigger()}
    <button
      class={downloadButtonTheme({
        size,
        variant,
        loading: isLoading,
      })}
      onclick={handleClick}
      disabled={isLoading}
      aria-label="Download image"
    >
      <span class="relative flex items-center justify-center">
        <Icon
          icon="ph:download-simple"
          class={downloadIconTheme({
            size,
            variant,
            loading: isLoading,
          })}
        />
      </span>
    </button>
  {/snippet}
  Download
</Tooltip>
