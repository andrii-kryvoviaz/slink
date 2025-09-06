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
    "data-highlighted:bg-blue-100 dark:data-highlighted:bg-blue-800/40 data-highlighted:text-blue-600 dark:data-highlighted:text-blue-300 data-[state=open]:bg-blue-100 dark:data-[state=open]:bg-blue-800/40 data-[state=open]:text-blue-600 dark:data-[state=open]:text-blue-300 outline-hidden [&_svg:not([class*='text-'])]:text-gray-500 dark:[&_svg:not([class*='text-'])]:text-gray-400 flex cursor-default select-none items-center gap-2 rounded-lg px-3 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-200 transition-all duration-150 hover:bg-blue-100 dark:hover:bg-blue-800/40 hover:text-blue-600 dark:hover:text-blue-300 data-[disabled]:pointer-events-none data-[inset]:pl-8 data-[disabled]:opacity-50 [&_svg:not([class*='size-'])]:size-4 [&_svg]:pointer-events-none [&_svg]:shrink-0",
    className,
  )}
  {...restProps}
>
  {@render children?.()}
  <ChevronRightIcon class="ml-auto size-4" />
</DropdownMenuPrimitive.SubTrigger>
