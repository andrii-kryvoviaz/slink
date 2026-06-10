<script lang="ts">
  import type { Snippet } from 'svelte';

  import { fade } from 'svelte/transition';

  interface Props {
    children: Snippet;
    actions?: Snippet;
  }

  let { children, actions }: Props = $props();
</script>

<div in:fade={{ duration: 400, delay: 50 }}>
  <div class="flex items-center justify-between">
    <div class="space-y-1">
      {@render children()}
    </div>

    {#if actions}
      <div class="flex items-center gap-3">
        {@render actions()}
      </div>
    {/if}
  </div>
</div>
