<script lang="ts">
  import { Badge } from '@slink/feature/Text';
  import { Tooltip } from '@slink/ui/components/tooltip';

  import Icon from '@iconify/svelte';

  import type { ShareListItemVariant } from '@slink/api/Response/Share/ShareListItemResponse';

  interface Props {
    variant: ShareListItemVariant | null | undefined;
  }

  let { variant }: Props = $props();

  const dimensions = $derived.by<string | null>(() => {
    if (!variant) return null;
    const { width, height } = variant;
    if (width && height) return `${width}×${height}`;
    if (width) return `${width}w`;
    if (height) return `${height}h`;
    return null;
  });

  const format = $derived(variant?.format?.toUpperCase() ?? null);

  const filterLabel = $derived.by<string | null>(() => {
    const raw = variant?.filter;
    if (!raw) return null;
    return raw.replace(/[-_]+/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
  });
</script>

{#if dimensions}
  <Tooltip side="top" size="sm">
    {#snippet trigger()}
      <Badge variant="default" size="xs" class="gap-1 leading-none font-mono">
        <Icon icon="ph:frame-corners" class="h-3 w-3" />
        <span>{dimensions}</span>
      </Badge>
    {/snippet}
    <div class="flex items-center gap-1.5">
      <Icon icon="ph:frame-corners" class="h-3.5 w-3.5 opacity-70" />
      <span>Image dimensions</span>
    </div>
  </Tooltip>
{/if}

{#if format}
  <Tooltip side="top" size="sm">
    {#snippet trigger()}
      <Badge variant="default" size="xs" class="gap-1 leading-none">
        <Icon icon="ph:file-image" class="h-3 w-3" />
        <span>{format}</span>
      </Badge>
    {/snippet}
    <div class="flex items-center gap-1.5">
      <Icon icon="ph:file-image" class="h-3.5 w-3.5 opacity-70" />
      <span>Image format</span>
    </div>
  </Tooltip>
{/if}

{#if filterLabel}
  <Tooltip side="top" size="sm">
    {#snippet trigger()}
      <Badge variant="default" size="xs" class="gap-1 leading-none">
        <Icon icon="ph:magic-wand" class="h-3 w-3" />
        <span>{filterLabel}</span>
      </Badge>
    {/snippet}
    <div class="flex items-center gap-1.5">
      <Icon icon="ph:magic-wand" class="h-3.5 w-3.5 opacity-70" />
      <span>Applied filter</span>
    </div>
  </Tooltip>
{/if}
