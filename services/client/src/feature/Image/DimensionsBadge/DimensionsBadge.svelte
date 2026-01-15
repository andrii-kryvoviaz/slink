<script lang="ts">
  import Icon from '@iconify/svelte';

  import {
    type DimensionsBadgeVariant,
    dimensionsBadgeContainerTheme,
    dimensionsBadgeIconTheme,
    dimensionsBadgeValueTheme,
  } from './DimensionsBadge.theme';

  interface Props {
    width: number;
    height: number;
    variant?: DimensionsBadgeVariant;
  }

  let { width, height, variant = 'overlay' }: Props = $props();
</script>

<div class={dimensionsBadgeContainerTheme({ variant })}>
  <Icon icon="ph:frame-corners" class={dimensionsBadgeIconTheme({ variant })} />
  <span class={dimensionsBadgeValueTheme({ variant })}>
    {width}×{height}
  </span>
</div>
