<script lang="ts">
  import { type SubtitleProps, SubtitleTheme } from '@slink/feature/Text';
  import type { Snippet } from 'svelte';

  let {
    size,
    class: customClass,
    children,
  }: SubtitleProps & {
    class?: string;
    children: Snippet;
  } = $props();
</script>

<p class="{SubtitleTheme({ size })} {customClass ?? ''}">
  {@render children()}
</p>
