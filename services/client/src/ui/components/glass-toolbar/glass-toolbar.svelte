<script lang="ts">
  import type { Snippet } from 'svelte';

  import { cn } from '@slink/utils/ui/index.js';

  interface Props {
    class?: string;
    children: Snippet;
  }

  let { class: className, children }: Props = $props();
</script>

<div
  class={cn(
    'inline-flex h-8 items-stretch overflow-hidden rounded-full bg-white/8',
    className,
  )}
>
  {@render children()}
</div>
