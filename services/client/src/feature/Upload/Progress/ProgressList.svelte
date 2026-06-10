<script lang="ts">
  import { ScrollArea } from '@slink/ui/components/scroll-area/index.js';
  import type { Snippet } from 'svelte';

  import { fly } from 'svelte/transition';

  import Item from './ProgressItem.svelte';
  import { useUploadProgress } from './context.svelte';

  interface Props {
    children?: Snippet;
  }

  let { children }: Props = $props();

  const progress = useUploadProgress();
</script>

<div class="relative">
  <ScrollArea maxHeight="md" orientation="vertical" type="scroll">
    <div class="space-y-2 pr-1">
      {#each progress.items as item, index (item.id)}
        <div in:fly={{ duration: 250, delay: 150 + index * 40, y: 8 }}>
          <Item {item} />
        </div>
      {/each}
    </div>
  </ScrollArea>

  {#if children}
    {@render children()}
  {/if}
</div>
