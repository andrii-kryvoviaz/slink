<script lang="ts">
  import { Command as CommandPrimitive } from 'bits-ui';
  import type { Snippet } from 'svelte';

  import { cn } from '@slink/utils/ui/index.js';

  import { getFilterContainerContext } from './context.svelte';
  import { filterItemVariants } from './filter.theme';

  interface Props {
    value: string;
    onSelect?: () => void;
    disabled?: boolean;
    keywords?: string[];
    class?: string;
    children?: Snippet;
  }

  let {
    value,
    onSelect,
    disabled = false,
    keywords,
    class: className,
    children,
  }: Props = $props();

  const container = getFilterContainerContext();
</script>

<CommandPrimitive.Item
  {value}
  {onSelect}
  {disabled}
  {keywords}
  class={cn(filterItemVariants({ variant: container.variant }), className)}
>
  {@render children?.()}
</CommandPrimitive.Item>
