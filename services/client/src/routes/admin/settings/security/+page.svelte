<script lang="ts">
  import {
    AccessSettings,
    SettingsPageLayout,
    UserSettings,
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
  let accessLoading = $derived(
    page.isLoading && page.categoryBeingSaved === 'access',
  );
  let userLoading = $derived(
    page.isLoading && page.categoryBeingSaved === 'user',
  );
</script>

<svelte:head>
  <title>{$t('pages.admin.settings.security.page_title')}</title>
</svelte:head>

<SettingsPageLayout
  title={$t('pages.admin.settings.security.title')}
  description={$t('pages.admin.settings.security.description')}
  isInitialized={page.isInitialized}
>
  <AccessSettings
    bind:settings={page.settings.access}
    defaultSettings={defaultSettings?.access}
    loading={accessLoading}
    onSave={page.handleSave}
  />

  <UserSettings
    bind:settings={page.settings.user}
    defaultSettings={defaultSettings?.user}
    loading={userLoading}
    onSave={page.handleSave}
  />
</SettingsPageLayout>
