<script lang="ts">
  import type { Snippet } from 'svelte';

  interface Props {
    title: string;
    description?: string;
    children?: Snippet<[]>;
  }

  let { title, description, children }: Props = $props();
</script>

<div>
  <h3 class="text-sm font-semibold text-slate-900 dark:text-white">
    {title}
  </h3>
  <p class="text-xs text-slate-600 dark:text-slate-400">
    {#if children}{@render children()}{:else}{description}{/if}
  </p>
</div>
