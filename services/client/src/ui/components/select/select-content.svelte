<script lang="ts">
  import { Select as SelectPrimitive } from 'bits-ui';

  import { type WithoutChild, cn } from '@slink/utils/ui/index.js';

  import SelectScrollDownButton from './select-scroll-down-button.svelte';
  import SelectScrollUpButton from './select-scroll-up-button.svelte';

  let {
    ref = $bindable(null),
    class: className,
    sideOffset = 4,
    portalProps,
    children,
    ...restProps
  }: WithoutChild<SelectPrimitive.ContentProps> & {
    portalProps?: SelectPrimitive.PortalProps;
  } = $props();
</script>

<SelectPrimitive.Portal {...portalProps}>
  <SelectPrimitive.Content
    bind:ref
    {sideOffset}
    data-slot="select-content"
    class={cn(
      'popover-panel bg-popover text-popover-foreground max-h-(--bits-select-content-available-height) origin-(--bits-select-content-transform-origin) relative min-w-[8rem] overflow-y-auto overflow-x-hidden data-[side=bottom]:translate-y-1 data-[side=left]:-translate-x-1 data-[side=right]:translate-x-1 data-[side=top]:-translate-y-1',
      className,
    )}
    {...restProps}
  >
    <SelectScrollUpButton />
    <SelectPrimitive.Viewport
      class={cn(
        'h-(--bits-select-anchor-height) min-w-(--bits-select-anchor-width) w-full scroll-my-1 p-1',
      )}
    >
      {@render children?.()}
    </SelectPrimitive.Viewport>
    <SelectScrollDownButton />
  </SelectPrimitive.Content>
</SelectPrimitive.Portal>
