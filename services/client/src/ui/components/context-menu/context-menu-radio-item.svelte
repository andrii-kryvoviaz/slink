<script lang="ts">
  import CircleIcon from '@lucide/svelte/icons/circle';
  import { ContextMenu as ContextMenuPrimitive } from 'bits-ui';

  import { type WithoutChild, cn } from '@slink/utils/ui/index.js';

  let {
    ref = $bindable(null),
    class: className,
    children: childrenProp,
    ...restProps
  }: WithoutChild<ContextMenuPrimitive.RadioItemProps> = $props();
</script>

<ContextMenuPrimitive.RadioItem
  bind:ref
  data-slot="context-menu-radio-item"
  class={cn(
    "data-highlighted:bg-accent data-highlighted:text-accent-foreground outline-hidden relative flex cursor-default select-none items-center gap-2 rounded-sm py-1.5 pl-8 pr-2 text-sm data-[disabled]:pointer-events-none data-[disabled]:opacity-50 [&_svg:not([class*='size-'])]:size-4 [&_svg]:pointer-events-none [&_svg]:shrink-0",
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
</ContextMenuPrimitive.RadioItem>
