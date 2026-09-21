<script lang="ts">
  import { formatShortDate } from '$lib/utils/date.svelte';
  import Icon from '@iconify/svelte';

  import {
    type OverlayBadgeVariant,
    overlayBadgeContainerTheme,
    overlayBadgeIconTheme,
    overlayBadgeValueTheme,
  } from '../OverlayBadge.theme';

  interface Props {
    date: number;
    variant?: OverlayBadgeVariant;
  }

  let { date, variant = 'overlay' }: Props = $props();

  const formattedDate = $derived(formatShortDate(new Date(date * 1000)));
</script>

<div
  role="img"
  class={overlayBadgeContainerTheme({ variant })}
  aria-label={`Saved on ${formattedDate}`}
>
  <Icon
    icon="ph:bookmark-simple-fill"
    class={overlayBadgeIconTheme({ variant })}
  />
  <span class={overlayBadgeValueTheme({ variant })}>
    {formattedDate}
  </span>
</div>
