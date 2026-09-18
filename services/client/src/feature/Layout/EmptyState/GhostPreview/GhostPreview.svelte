<script lang="ts">
  import type { ViewMode } from '@slink/lib/settings';

  import GhostGrid from '../GhostGrid/GhostGrid.svelte';
  import GhostList from '../GhostList/GhostList.svelte';

  interface Props {
    mode: ViewMode;
  }

  let { mode }: Props = $props();
</script>

{#if mode === 'grid'}
  <GhostGrid />
{:else}
  <GhostList />
{/if}
