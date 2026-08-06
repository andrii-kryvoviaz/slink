<script lang="ts">
  import ChevronRightIcon from '@lucide/svelte/icons/chevron-right';
  import { DropdownMenu as DropdownMenuPrimitive } from 'bits-ui';

  import { cn } from '@slink/utils/ui/index.js';

  let {
    ref = $bindable(null),
    class: className,
    inset,
    children,
    ...restProps
  }: DropdownMenuPrimitive.SubTriggerProps & {
    inset?: boolean;
  } = $props();
</script>

<DropdownMenuPrimitive.SubTrigger
  bind:ref
  data-slot="dropdown-menu-sub-trigger"
  data-inset={inset}
  class={cn(
    "data-highlighted:bg-info-subtle data-highlighted:text-info-subtle-foreground data-[state=open]:bg-info-subtle data-[state=open]:text-info-subtle-foreground outline-hidden [&_svg:not([class*='text-'])]:text-muted-foreground flex cursor-default select-none items-center gap-2 rounded-lg px-3 py-2.5 text-sm font-medium text-foreground-soft transition-all duration-150 hover:bg-info-subtle hover:text-info-subtle-foreground data-[disabled]:pointer-events-none data-[inset]:pl-8 data-[disabled]:opacity-50 [&_svg:not([class*='size-'])]:size-4 [&_svg]:pointer-events-none [&_svg]:shrink-0",
    className,
  )}
  {...restProps}
>
  {@render children?.()}
  <ChevronRightIcon class="ml-auto size-4" />
</DropdownMenuPrimitive.SubTrigger>
