<script lang="ts">
  import CheckIcon from '@lucide/svelte/icons/check';
  import { Select as SelectPrimitive } from 'bits-ui';

  import { type WithoutChild, cn } from '@slink/utils/ui/index.js';

  let {
    ref = $bindable(null),
    class: className,
    value,
    label,
    children: childrenProp,
    ...restProps
  }: WithoutChild<SelectPrimitive.ItemProps> = $props();
</script>

<SelectPrimitive.Item
  bind:ref
  {value}
  data-slot="select-item"
  class={cn(
    "data-highlighted:bg-info-wash dark:data-highlighted:bg-info-wash/20 data-highlighted:text-info-text [&_svg:not([class*='text-'])]:text-info data-highlighted:[&_svg:not([class*='text-'])]:text-info-text outline-hidden *:[span]:last:flex *:[span]:last:items-center *:[span]:last:gap-2 relative flex w-full cursor-default select-none items-center gap-2 rounded-lg py-2.5 pl-3 pr-8 text-sm font-medium text-foreground-soft transition-all duration-150 hover:bg-info-wash dark:hover:bg-info-wash/20 hover:text-info-text data-[disabled]:pointer-events-none data-[disabled]:opacity-50 [&_svg:not([class*='size-'])]:size-4 [&_svg]:pointer-events-none [&_svg]:shrink-0",
    className,
  )}
  {...restProps}
>
  {#snippet children({ selected, highlighted })}
    <span class="absolute right-2 flex size-3.5 items-center justify-center">
      {#if selected}
        <CheckIcon class="size-4" />
      {/if}
    </span>
    {#if childrenProp}
      {@render childrenProp({ selected, highlighted })}
    {:else}
      {label || value}
    {/if}
  {/snippet}
</SelectPrimitive.Item>
