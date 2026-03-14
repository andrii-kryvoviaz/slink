<script lang="ts">
  import { type TitleProps, TitleTheme } from '@slink/feature/Text';
  import type { Snippet } from 'svelte';

  let {
    as = 'h1',
    size,
    weight,
    class: customClass,
    children,
  }: TitleProps & {
    as?: 'h1' | 'h2' | 'h3' | 'h4' | 'h5' | 'h6';
    class?: string;
    children: Snippet;
  } = $props();
</script>

<svelte:element
  this={as}
  class="{TitleTheme({ size, weight })} {customClass ?? ''}"
>
  {@render children()}
</svelte:element>
