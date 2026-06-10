<script lang="ts">
  import type { Snippet } from 'svelte';

  import { className } from '$lib/utils/ui/className';

  interface Props {
    class?: string;
    children: Snippet;
  }

  let { class: customClass, children }: Props = $props();
</script>

<div class={className('flex flex-col gap-4', customClass)}>
  {@render children()}
</div>
