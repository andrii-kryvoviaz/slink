<script lang="ts">
  import * as HoverCard from '@slink/ui/components/hover-card';
  import type { Snippet } from 'svelte';

  import Icon from '@iconify/svelte';

  import type { ShareListItemVariant } from '@slink/api/Response/Share/ShareListItemResponse';

  import { dimensionsLabel, filterLabel, formatLabel } from '../variantLabels';
  import { shareAttributeChips } from './ShareAttributeChips.theme';

  interface Props {
    variant: ShareListItemVariant | null | undefined;
    requiresPassword: boolean;
  }

  let { variant, requiresPassword }: Props = $props();

  const dimensions = $derived(dimensionsLabel(variant));
  const format = $derived(formatLabel(variant));
  const filter = $derived(filterLabel(variant));

  const hasAny = $derived(
    dimensions !== null ||
      format !== null ||
      filter !== null ||
      requiresPassword,
  );

  const theme = shareAttributeChips();
</script>

{#snippet chip(
  icon: string,
  tone: 'muted' | 'accent',
  value: string | null,
  mono: boolean,
  label: Snippet,
)}
  <HoverCard.Root openDelay={300} closeDelay={100}>
    <HoverCard.Trigger class="cursor-pointer">
      <span class={theme.chip({ tone })}>
        <Icon {icon} class={theme.icon()} />
      </span>
    </HoverCard.Trigger>
    <HoverCard.Content side="bottom" align="start" variant="glass" size="sm">
      <p class={theme.label()}>{@render label()}</p>
      {#if value !== null}
        <p class={theme.value({ mono })}>{value}</p>
      {/if}
    </HoverCard.Content>
  </HoverCard.Root>
{/snippet}

{#snippet dimensionsText()}Image dimensions{/snippet}
{#snippet formatText()}Image format{/snippet}
{#snippet filterText()}Applied filter{/snippet}
{#snippet protectedText()}Password required to open this link{/snippet}

<div class={theme.root()}>
  {#if dimensions !== null}
    {@render chip(
      'ph:frame-corners',
      'muted',
      dimensions,
      true,
      dimensionsText,
    )}
  {/if}

  {#if format !== null}
    {@render chip('ph:file-image', 'muted', format, false, formatText)}
  {/if}

  {#if filter !== null}
    {@render chip('ph:magic-wand', 'muted', filter, false, filterText)}
  {/if}

  {#if requiresPassword}
    {@render chip('ph:lock-simple', 'accent', null, false, protectedText)}
  {/if}

  {#if !hasAny}
    <span class={theme.empty()}>—</span>
  {/if}
</div>
