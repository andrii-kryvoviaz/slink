<script lang="ts">
  import CircleIcon from '@lucide/svelte/icons/circle';
  import { DropdownMenu as DropdownMenuPrimitive } from 'bits-ui';

  import { type WithoutChild, cn } from '@slink/utils/ui/index.js';

  let {
    ref = $bindable(null),
    class: className,
    children: childrenProp,
    ...restProps
  }: WithoutChild<DropdownMenuPrimitive.RadioItemProps> = $props();
</script>

<DropdownMenuPrimitive.RadioItem
  bind:ref
  data-slot="dropdown-menu-radio-item"
  class={cn(
    "focus:bg-info-wash dark:focus:bg-info-wash/20 focus:text-info-text outline-hidden relative flex cursor-default select-none items-center gap-2 rounded-lg py-2.5 pl-8 pr-3 text-sm font-medium text-foreground-soft transition-all duration-150 hover:bg-info-wash dark:hover:bg-info-wash/20 hover:text-info-text data-[disabled]:pointer-events-none data-[disabled]:opacity-50 [&_svg:not([class*='size-'])]:size-4 [&_svg]:pointer-events-none [&_svg]:shrink-0",
    className,
  )}
  {...restProps}
>
  {#snippet children({ checked })}
    <span
      class="pointer-events-none absolute left-2 flex size-3.5 items-center justify-center"
    >
      {#if checked}
        <CircleIcon class="size-2 fill-current" />
      {/if}
    </span>
    {@render childrenProp?.({ checked })}
  {/snippet}
</DropdownMenuPrimitive.RadioItem>
