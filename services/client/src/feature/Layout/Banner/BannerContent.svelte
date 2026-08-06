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
  <h3 class="text-sm font-semibold text-foreground">
    {title}
  </h3>
  <p class="text-xs text-muted-foreground">
    {#if children}{@render children()}{:else}{description}{/if}
  </p>
</div>
