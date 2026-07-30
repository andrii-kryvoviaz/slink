<script lang="ts">
  import { Separator as SeparatorPrimitive } from 'bits-ui';

  import { cn } from '@slink/utils/ui/index.js';

  import { getToolbarContext } from './context';
  import { toolbarSeparatorVariants } from './toolbar.theme';

  let { class: className, ...restProps }: SeparatorPrimitive.RootProps =
    $props();

  const context = getToolbarContext();
</script>

<SeparatorPrimitive.Root
  orientation="vertical"
  decorative
  class={cn(
    toolbarSeparatorVariants({ tone: context?.tone ?? 'dark' }),
    className,
  )}
  {...restProps}
/>
