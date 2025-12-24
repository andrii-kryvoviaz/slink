<script lang="ts">
  import type { Snippet } from 'svelte';

  import type { HTMLAnchorAttributes } from 'svelte/elements';

  import { type WithElementRef, cn } from '@slink/utils/ui/index.js';

  let {
    ref = $bindable(null),
    children,
    child,
    class: className,
    size = 'md',
    isActive = false,
    ...restProps
  }: WithElementRef<HTMLAnchorAttributes> & {
    child?: Snippet<[{ props: Record<string, unknown> }]>;
    size?: 'sm' | 'md';
    isActive?: boolean;
  } = $props();

  const mergedProps = $derived({
    class: cn(
      'text-sidebar-foreground/80 ring-sidebar-ring outline-hidden flex h-7 min-w-0 items-center gap-2 overflow-hidden rounded-r-md pl-3 pr-2 transition-all duration-200 focus-visible:ring-2 focus-visible:ring-indigo-500/20 disabled:pointer-events-none disabled:opacity-50 aria-disabled:pointer-events-none aria-disabled:opacity-50 [&>span:last-child]:truncate [&>svg]:size-4 [&>svg]:shrink-0',
      'border-l-2 border-sidebar-border',
      'hover:text-sidebar-foreground hover:border-indigo-400/60 dark:hover:border-indigo-500/60',
      'data-[active=true]:border-indigo-600 dark:data-[active=true]:border-indigo-400 data-[active=true]:text-indigo-700 dark:data-[active=true]:text-indigo-300 data-[active=true]:font-medium',
      size === 'sm' && 'text-xs',
      size === 'md' && 'text-sm',
      'group-data-[collapsible=icon]:hidden',
      className,
    ),
    'data-slot': 'sidebar-menu-sub-button',
    'data-sidebar': 'menu-sub-button',
    'data-size': size,
    'data-active': isActive,
    ...restProps,
  });
</script>

{#if child}
  {@render child({ props: mergedProps })}
{:else}
  <a bind:this={ref} {...mergedProps}>
    {@render children?.()}
  </a>
{/if}
