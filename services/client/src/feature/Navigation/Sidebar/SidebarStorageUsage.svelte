<script lang="ts">
  import StorageUsageDisplay from '@slink/feature/Storage/StorageUsageWidget/StorageUsageDisplay.svelte';
  import * as HoverCard from '@slink/ui/components/hover-card/index.js';
  import { fade } from 'svelte/transition';
  import { useStorageUsageStore } from '@slink/lib/state/StorageUsageStore.svelte';
  import { onMount } from 'svelte';

  import { page } from '$app/state';
  import Icon from '@iconify/svelte';

  import { useSidebar } from '@slink/ui/components/sidebar/index.js';

  const sidebar = useSidebar();
  const storageStore = useStorageUsageStore();

  onMount(() => {
    if (storageStore.isDisabled) return;
    
    if (storageStore.isEmpty && !storageStore.isLoading) {
      storageStore.load();
    }
  });

  const storageData = $derived(storageStore.usage || page.data?.storageUsage);
  const isLoading = $derived(storageStore.isLoading);
  const error = $derived(storageStore.hasError ? 'Failed to load storage usage' : null);
</script>

{#if !storageStore.isDisabled}
  {#if sidebar.state === 'expanded'}
    <div in:fade={{ duration: 300, delay: 200 }}>
      <StorageUsageDisplay 
        data={storageData} 
        {isLoading} 
        {error} 
      />
    </div>
  {/if}
  <div class="hidden group-data-[collapsible=icon]:block">
    <HoverCard.Root openDelay={200} closeDelay={100}>
      <HoverCard.Trigger
        class="flex w-full items-center gap-2 overflow-hidden rounded-md p-2 text-left text-sm transition-all duration-300 ease-out text-muted-foreground hover:bg-sidebar-accent hover:text-sidebar-accent-foreground justify-center size-8"
      >
        <Icon
          icon="heroicons:server"
          class="size-4 shrink-0"
        />
      </HoverCard.Trigger>
      <HoverCard.Content side="right" align="end" class="w-80 border-0 p-0">
        <StorageUsageDisplay 
          data={storageData} 
          {isLoading} 
          {error} 
        />
      </HoverCard.Content>
    </HoverCard.Root>
  </div>
{/if}