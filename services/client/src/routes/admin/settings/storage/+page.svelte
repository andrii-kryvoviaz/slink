<script lang="ts">
  import {
    CacheSettings,
    SettingsPageLayout,
    StorageSettings,
  } from '@slink/feature/Settings';

  import { t } from '$lib/i18n';

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
  <title>{$t('pages.admin.settings.storage.page_title')}</title>
</svelte:head>

<SettingsPageLayout
  title={$t('pages.admin.settings.storage.title')}
  description={$t('pages.admin.settings.storage.description')}
  isInitialized={page.isInitialized}
>
  <StorageSettings
    bind:settings={page.settings.storage}
    defaultSettings={defaultSettings?.storage}
    loading={storageLoading}
    onSave={page.handleSave}
  />

  <CacheSettings />
</SettingsPageLayout>
