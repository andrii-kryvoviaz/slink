<script lang="ts">
  import StorageUsageDisplay from '@slink/feature/Storage/StorageUsageWidget/StorageUsageDisplay.svelte';
  import * as HoverCard from '@slink/ui/components/hover-card/index.js';
  import { useSidebar } from '@slink/ui/components/sidebar/index.js';
  import { onMount } from 'svelte';

  import { page } from '$app/state';
  import Icon from '@iconify/svelte';
  import { fade } from 'svelte/transition';

  import { useStorageUsageStore } from '@slink/lib/state/StorageUsageStore.svelte';

  const sidebar = useSidebar();
  const storageStore = useStorageUsageStore();

  onMount(() => {
    if (storageStore.isDisabled) return;

    if (storageStore.isEmpty && !storageStore.isLoading) {
      storageStore.load();
    }
  });

  const storageData = $derived(storageStore.usage || page.data?.storageUsage);
  const error = $derived(
    storageStore.hasError ? 'Failed to load storage usage' : null,
  );
  const shouldShow = $derived(storageData || error);
</script>

{#if !storageStore.isDisabled && shouldShow}
  {#if sidebar.state === 'expanded'}
    <div in:fade={{ duration: 300, delay: 200 }}>
      <StorageUsageDisplay data={storageData} {error} />
    </div>
  {/if}
  <div class="hidden group-data-[collapsible=icon]:block">
    <HoverCard.Root openDelay={200} closeDelay={100}>
      <HoverCard.Trigger
        class="flex w-full items-center gap-2 overflow-hidden rounded-md p-2 text-left text-sm transition-all duration-300 ease-out text-muted-foreground hover:bg-sidebar-accent hover:text-sidebar-accent-foreground justify-center size-8"
      >
        <Icon icon="heroicons:server" class="size-4 shrink-0" />
      </HoverCard.Trigger>
      <HoverCard.Content side="right" align="end" class="w-80 border-0 p-0">
        <StorageUsageDisplay data={storageData} {error} />
      </HoverCard.Content>
    </HoverCard.Root>
  </div>
{/if}
