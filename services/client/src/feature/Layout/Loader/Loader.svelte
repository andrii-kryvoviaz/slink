<script lang="ts">
  import { type LoaderProps, LoaderTheme } from '@slink/feature/Layout/index';
  import type { Snippet } from 'svelte';

  import { className } from '$lib/utils/ui/className';

  interface Props extends LoaderProps {
    class?: string;
    children?: Snippet;
  }

  let {
    variant = 'default',
    size = 'sm',
    speed = 'default',
    children,
    ...props
  }: Props = $props();

  const defaultOuterClasses = 'flex items-center justify-center gap-4';

  let innerClasses = $derived(className(LoaderTheme({ variant, size, speed })));
  let outerClasses = $derived(className(defaultOuterClasses, props.class));
</script>

<div class={outerClasses}>
  <div {...props} class={innerClasses}></div>
  {@render children?.()}
</div>
