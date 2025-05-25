<script lang="ts">
  import type { Snippet } from 'svelte';

  interface Props {
    children?: Snippet;
  }

  let { children }: Props = $props();
</script>

<div
  class="w-full rounded-lg bg-neutral-100/95 px-4 py-6 text-zinc-800 shadow-md backdrop-blur-sm dark:bg-zinc-900/95 dark:text-gray-300"
>
  {@render children?.()}
</div>
