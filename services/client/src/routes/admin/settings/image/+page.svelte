<script lang="ts">
  import {
    ImageSettings,
    SettingsPageLayout,
    ShareSettings,
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
  let imageLoading = $derived(
    page.isLoading && page.categoryBeingSaved === 'image',
  );
  let shareLoading = $derived(
    page.isLoading && page.categoryBeingSaved === 'share',
  );
</script>

<svelte:head>
  <title>{$t('pages.admin.settings.image.page_title')}</title>
</svelte:head>

<SettingsPageLayout
  title={$t('pages.admin.settings.image.title')}
  description={$t('pages.admin.settings.image.description')}
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
