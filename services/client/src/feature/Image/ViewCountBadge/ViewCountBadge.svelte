<script lang="ts">
  import Icon from '@iconify/svelte';

  import {
    type ViewCountBadgeVariant,
    viewCountBadgeContainerTheme,
    viewCountBadgeIconTheme,
    viewCountBadgeIconWrapperTheme,
    viewCountBadgeLabelTheme,
    viewCountBadgeValueTheme,
  } from './ViewCountBadge.theme';

  interface Props {
    count: number;
    variant?: ViewCountBadgeVariant;
    showLabel?: boolean;
  }

  let { count, variant = 'card', showLabel = false }: Props = $props();

  const label = $derived(count === 1 ? 'view' : 'views');
</script>

{#if variant !== 'compact' && variant !== 'overlay'}
  <div class={viewCountBadgeContainerTheme({ variant })}>
    <div class={viewCountBadgeIconWrapperTheme({ variant })}>
      <Icon icon="heroicons:eye" class={viewCountBadgeIconTheme({ variant })} />
    </div>
    <div class="flex flex-col min-w-0 flex-1">
      <span class={viewCountBadgeLabelTheme({ variant })}> Views </span>
      <span class={viewCountBadgeValueTheme({ variant })}>
        {count.toLocaleString()}
        {#if showLabel}
          {label}
        {/if}
      </span>
    </div>
  </div>
{:else}
  <div class={viewCountBadgeContainerTheme({ variant })}>
    <Icon icon="heroicons:eye" class={viewCountBadgeIconTheme({ variant })} />
    <span class={viewCountBadgeValueTheme({ variant })}>
      {count.toLocaleString()}
    </span>
  </div>
{/if}
