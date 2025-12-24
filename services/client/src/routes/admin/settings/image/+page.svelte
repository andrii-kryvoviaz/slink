<script lang="ts">
  import {
    ImageSettings,
    SettingsPageLayout,
    ShareSettings,
  } from '@slink/feature/Settings';

  import { useSettingsPage } from '@slink/lib/state/SettingsPage.svelte';

  import type { PageData } from './$types';

  interface Props {
    data: PageData;
  }

  let { data }: Props = $props();

  const page = useSettingsPage();
  let defaultSettings = $derived(data?.defaultSettings);
  let imageLoading = $derived(
    page.isLoading && page.categoryBeingSaved === 'image',
  );
  let shareLoading = $derived(
    page.isLoading && page.categoryBeingSaved === 'share',
  );
</script>

<svelte:head>
  <title>Image Settings | Slink</title>
</svelte:head>

<SettingsPageLayout
  title="Image"
  description="Configure image handling and sharing preferences"
  isInitialized={page.isInitialized}
>
  <ImageSettings
    bind:settings={page.settings.image}
    defaultSettings={defaultSettings?.image}
    loading={imageLoading}
    onSave={page.handleSave}
  />

  <ShareSettings
    bind:settings={page.settings.share}
    defaultSettings={defaultSettings?.share}
    loading={shareLoading}
    onSave={page.handleSave}
  />
</SettingsPageLayout>
