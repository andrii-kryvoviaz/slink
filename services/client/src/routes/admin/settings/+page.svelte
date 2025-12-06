<script lang="ts">
  import {
    AccessSettings,
    ImageSettings,
    ShareSettings,
    StorageSettings,
    UserSettings,
  } from '@slink/feature/Settings';

  import { ApiClient } from '@slink/api/Client';
  import { ReactiveState } from '@slink/api/ReactiveState';
  import type { EmptyResponse } from '@slink/api/Response';

  import type {
    SettingCategory,
    SettingCategoryData,
  } from '@slink/lib/settings/Type/GlobalSettings';
  import { useGlobalSettings } from '@slink/lib/state/GlobalSettings.svelte';

  import { printErrorsAsToastMessage } from '@slink/utils/ui/printErrorsAsToastMessage';

  import type { PageServerData } from './$types';

  interface Props {
    data: PageServerData;
  }

  let { data }: Props = $props();

  const globalSettingsManager = useGlobalSettings();

  const {
    run: saveSettings,
    isLoading,
    error,
  } = ReactiveState<EmptyResponse>(
    (category: SettingCategory, data: SettingCategoryData) => {
      return ApiClient.setting.updateSettings(category, data);
    },
    { debounce: 300, minExecutionTime: 500 },
  );

  let categoryBeingSaved: SettingCategory | null = $state(null);

  const handleSettingsSectionSave = async ({
    category,
  }: {
    category: SettingCategory;
  }) => {
    const { [category]: data } = settings;
    categoryBeingSaved = category;

    await saveSettings(category, data);

    if (!$error) {
      globalSettingsManager.updateCategory(category, data);
    }
  };

  let settings = $derived(globalSettingsManager.settings);
  let defaultSettings = $state(data?.defaultSettings);

  $effect(() => {
    if ($error) {
      printErrorsAsToastMessage($error);
    }
  });
</script>

<svelte:head>
  <title>Settings | Slink</title>
</svelte:head>

{#if globalSettingsManager.isInitialized}
  <div class="flex flex-col w-full max-w-4xl px-8 py-8 space-y-12">
    <div class="space-y-3">
      <h1
        class="text-3xl font-light text-gray-900 dark:text-white tracking-tight"
      >
        Settings
      </h1>
      <p
        class="text-gray-600 dark:text-gray-400 text-base leading-relaxed max-w-2xl"
      >
        Configure your application preferences and manage system behavior
      </p>
    </div>

    <div class="space-y-16">
      <ImageSettings
        bind:settings={settings.image}
        defaultSettings={defaultSettings?.image}
        loading={$isLoading && categoryBeingSaved === 'image'}
        onSave={handleSettingsSectionSave}
      />

      <AccessSettings
        bind:settings={settings.access}
        defaultSettings={defaultSettings?.access}
        loading={$isLoading && categoryBeingSaved === 'access'}
        onSave={handleSettingsSectionSave}
      />

      <ShareSettings
        bind:settings={settings.share}
        defaultSettings={defaultSettings?.share}
        loading={$isLoading && categoryBeingSaved === 'share'}
        onSave={handleSettingsSectionSave}
      />

      <StorageSettings
        bind:settings={settings.storage}
        defaultSettings={defaultSettings?.storage}
        loading={$isLoading && categoryBeingSaved === 'storage'}
        onSave={handleSettingsSectionSave}
      />

      <UserSettings
        bind:settings={settings.user}
        defaultSettings={defaultSettings?.user}
        loading={$isLoading && categoryBeingSaved === 'user'}
        onSave={handleSettingsSectionSave}
      />
    </div>
  </div>
{/if}
