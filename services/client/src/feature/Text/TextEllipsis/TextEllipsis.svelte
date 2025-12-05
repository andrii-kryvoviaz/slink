<script lang="ts">
  import type { Snippet } from 'svelte';

  import { cn } from '$lib/utils/ui';

  import {
    type TextEllipsisFadeVariants,
    type TextEllipsisVariants,
    textEllipsisFadeTheme,
    textEllipsisTheme,
  } from './TextEllipsis.theme';

  interface Props extends TextEllipsisVariants, TextEllipsisFadeVariants {
    class?: string;
    children?: Snippet;
  }

  let {
    maxWidth = 'md',
    background = 'default',
    children,
    ...props
  }: Props = $props();
</script>

<span class="relative grow overflow-hidden text-ellipsis break-all">
  <span class={cn(textEllipsisTheme({ maxWidth }), props.class)}>
    {@render children?.()}
    &nbsp; &nbsp; &nbsp;
    <span class={textEllipsisFadeTheme({ background })}></span>
  </span>
</span>
