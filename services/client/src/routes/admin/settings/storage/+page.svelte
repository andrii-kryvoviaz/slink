<script lang="ts">
  import { SettingsPageLayout, StorageSettings } from '@slink/feature/Settings';

  import { useSettingsPage } from '@slink/lib/state/SettingsPage.svelte';

  import type { PageData } from './$types';

  interface Props {
    data: PageData;
  }

  let { data }: Props = $props();

  const page = useSettingsPage();
  let defaultSettings = $derived(data?.defaultSettings);
  let storageLoading = $derived(
    page.isLoading && page.categoryBeingSaved === 'storage',
  );
</script>

<svelte:head>
  <title>Storage Settings | Slink</title>
</svelte:head>

<SettingsPageLayout
  title="Storage"
  description="Configure storage providers and cache management"
  isInitialized={page.isInitialized}
>
  <StorageSettings
    bind:settings={page.settings.storage}
    defaultSettings={defaultSettings?.storage}
    loading={storageLoading}
    onSave={page.handleSave}
  />
</SettingsPageLayout>
