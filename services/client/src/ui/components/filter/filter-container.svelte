<script lang="ts">
  import type { Snippet } from 'svelte';

  import { cn } from '@slink/utils/ui/index.js';

  import { setFilterContainerContext } from './context.svelte';
  import {
    type FilterRounded,
    type FilterSize,
    type FilterVariant,
    filterContainerVariants,
  } from './filter.theme';

  interface Props {
    variant?: FilterVariant;
    size?: FilterSize;
    rounded?: FilterRounded;
    disabled?: boolean;
    open?: boolean;
    wrap?: boolean;
    hasActiveSummary?: boolean;
    role?: string;
    'aria-expanded'?: boolean | undefined;
    'aria-disabled'?: boolean;
    tabindex?: number;
    onclick?: (event: MouseEvent) => void;
    onkeydown?: (event: KeyboardEvent) => void;
    class?: string;
    children?: Snippet;
    [key: string]: unknown;
  }

  let {
    variant = 'default',
    size = 'md',
    rounded = 'lg',
    disabled = false,
    open = false,
    wrap = false,
    hasActiveSummary = false,
    role,
    tabindex,
    onclick,
    onkeydown,
    class: className,
    children,
    ...rest
  }: Props = $props();

  setFilterContainerContext({
    variant,
    size,
    disabled: () => disabled,
  });
</script>

<!-- svelte-ignore a11y_no_noninteractive_tabindex -->
<div
  {...rest}
  {role}
  {tabindex}
  {onclick}
  {onkeydown}
  class={cn(
    filterContainerVariants({
      variant,
      size,
      rounded,
      disabled,
      open,
      wrap,
      hasActiveSummary,
    }),
    className,
  )}
>
  {@render children?.()}
</div>
