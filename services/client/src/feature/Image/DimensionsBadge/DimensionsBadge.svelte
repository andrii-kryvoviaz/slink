<script lang="ts">
  import Icon from '@iconify/svelte';

  import {
    type OverlayBadgeVariant,
    overlayBadgeContainerTheme,
    overlayBadgeIconTheme,
    overlayBadgeValueTheme,
  } from '../OverlayBadge.theme';

  interface Props {
    width: number;
    height: number;
    variant?: OverlayBadgeVariant;
  }

  let { width, height, variant = 'overlay' }: Props = $props();
</script>

<div class={overlayBadgeContainerTheme({ variant })}>
  <Icon icon="ph:frame-corners" class={overlayBadgeIconTheme({ variant })} />
  <span class={overlayBadgeValueTheme({ variant })}>
    {width}×{height}
  </span>
</div>
