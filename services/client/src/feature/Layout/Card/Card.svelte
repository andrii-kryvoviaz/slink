<script lang="ts">
  import { type CardProps, CardTheme } from '@slink/feature/Layout';
  import type { Snippet } from 'svelte';

  import { className } from '$lib/utils/ui/className';

  interface Props extends CardProps {
    class?: string;
    children?: Snippet;
  }

  let {
    variant = 'default',
    size = 'full',
    rounded = 'md',
    shadow = 'md',
    children,
    ...props
  }: Props = $props();

  let classes = $derived(
    className(
      `${CardTheme({ variant, size, rounded, shadow })} ${props.class}`,
    ),
  );
</script>

<div class={classes}>
  {@render children?.()}
</div>
