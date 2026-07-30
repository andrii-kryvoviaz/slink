<script lang="ts">
  import { Toolbar as ToolbarPrimitive } from 'bits-ui';

  import { cn } from '@slink/utils/ui/index.js';

  import { type ToolbarTone, setToolbarContext } from './context';
  import { toolbarVariants } from './toolbar.theme';

  interface Props extends ToolbarPrimitive.RootProps {
    tone?: ToolbarTone;
  }

  let {
    tone = 'dark',
    class: className,
    children,
    ...restProps
  }: Props = $props();

  setToolbarContext({ tone, surface: 'toolbar', withinRoot: true });
</script>

<ToolbarPrimitive.Root
  class={cn(toolbarVariants({ tone }), className)}
  {...restProps}
>
  {@render children?.()}
</ToolbarPrimitive.Root>
