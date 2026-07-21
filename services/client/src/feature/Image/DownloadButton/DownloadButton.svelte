<script lang="ts">
  import * as Toolbar from '@slink/ui/components/toolbar';
  import { Tooltip, type TooltipVariant } from '@slink/ui/components/tooltip';
  import { mergeProps } from 'bits-ui';

  import { downloadByLink } from '$lib/utils/http/downloadByLink';
  import { useAutoReset } from '$lib/utils/time/useAutoReset.svelte';
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

  const loadingState = useAutoReset(500);

  const handleClick = async (e: MouseEvent) => {
    e.stopPropagation();
    e.preventDefault();

    if (loadingState.active) return;

    loadingState.trigger();

    downloadByLink(imageUrl, fileName);
  };
</script>

{#snippet content()}
  <span class="relative flex items-center justify-center">
    <Icon
      icon="ph:download-simple"
      class={downloadIconTheme({
        size,
        variant,
        loading: loadingState.active,
      })}
    />
  </span>
{/snippet}

{#if variant === 'toolbar' || variant === 'overlay'}
  <Tooltip
    side="top"
    sideOffset={6}
    collisionPadding={8}
    variant={tooltipVariant}
  >
    {#snippet triggerChild({ props })}
      <Toolbar.Button
        {...mergeProps(props, { onclick: handleClick })}
        class="group/download"
        loading={loadingState.active}
        disabled={loadingState.active}
        aria-label="Download image"
      >
        {@render content()}
      </Toolbar.Button>
    {/snippet}
    Download
  </Tooltip>
{:else}
  <Tooltip
    side="top"
    sideOffset={6}
    collisionPadding={8}
    variant={tooltipVariant}
  >
    {#snippet trigger()}
      <button
        class={downloadButtonTheme({
          size,
          variant,
          loading: loadingState.active,
        })}
        onclick={handleClick}
        disabled={loadingState.active}
        aria-label="Download image"
      >
        {@render content()}
      </button>
    {/snippet}
    Download
  </Tooltip>
{/if}
