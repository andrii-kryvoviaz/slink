<script lang="ts">
  import { Loader } from '@slink/feature/Layout';
  import { SettingItem } from '@slink/feature/Settings';
  import { Notice } from '@slink/feature/Text';
  import { Select } from '@slink/ui/components';
  import { Button } from '@slink/ui/components/button';
  import { Switch } from '@slink/ui/components/switch';

  import { enhance } from '$app/forms';
  import Icon from '@iconify/svelte';
  import { fade } from 'svelte/transition';

  import type { UserPreferencesResponse } from '@slink/api/Response';

  import type { User } from '@slink/lib/auth/Type/User';
  import { LandingPage } from '@slink/lib/enum/LandingPage';
  import type { License } from '@slink/lib/enum/License';

  import { withLoadingState } from '@slink/utils/form/withLoadingState';
  import { useWritable } from '@slink/utils/store/contextAwareStore';
  import { toast } from '@slink/utils/ui/toast-sonner.svelte';

  interface PageData {
    user: User;
    preferences: UserPreferencesResponse;
    licenses: License[];
    licensingEnabled: boolean;
  }

  interface Props {
    data: PageData;
    form: any;
  }

  let { data, form }: Props = $props();

  let licenses = $derived(data.licenses);
  let selectedLicense = $state('');
  let selectedLandingPage = $state(LandingPage.Explore);
  let syncToImages = $state(false);

  $effect(() => {
    selectedLicense = data.preferences?.['license.default'] ?? '';
    selectedLandingPage =
      data.preferences?.['navigation.landingPage'] ?? LandingPage.Explore;
  });

  let selectedLicenseInfo = $derived(
    licenses.find((l) => l.id === selectedLicense),
  );

  const landingPageOptions = [
    { value: LandingPage.Explore, label: 'Explore' },
    { value: LandingPage.Upload, label: 'Upload' },
  ];

  let isPreferencesFormLoading = useWritable(
    'updatePreferencesFormLoadingState',
    false,
  );

  $effect(() => {
    if (form?.preferencesWasUpdated) {
      toast.success('Preferences updated successfully');
      syncToImages = false;
    }
  });

  $effect(() => {
    if (form?.errors?.message) {
      toast.error(form.errors.message);
    }
  });

  const licenseOptions = $derived(
    licenses.map((license) => ({
      value: license.id,
      label: license.title,
    })),
  );
</script>

<svelte:head>
  <title>Preferences | Slink</title>
</svelte:head>

<div
  class="flex flex-col w-full max-w-2xl px-6 py-8"
  in:fade={{ duration: 150 }}
>
  <header class="mb-8">
    <h1 class="text-xl font-semibold text-gray-900 dark:text-white">
      Preferences
    </h1>
    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
      Configure your default settings and preferences
    </p>
  </header>

  <form
    action="?/updatePreferences"
    method="POST"
    use:enhance={withLoadingState(isPreferencesFormLoading)}
  >
    <div class="space-y-8">
      <section class="space-y-1">
        <div class="flex items-center justify-between gap-4 pb-3">
          <h2
            class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
          >
            Navigation
          </h2>
        </div>

        <div
          class="divide-y divide-gray-100 dark:divide-gray-800 rounded-xl bg-gray-50/50 dark:bg-gray-900/30 border border-gray-100 dark:border-gray-800 overflow-hidden"
        >
          <SettingItem>
            {#snippet label()}
              Default Landing Page
            {/snippet}
            {#snippet hint()}
              The page to show when you visit the site
            {/snippet}
            <Select
              items={landingPageOptions}
              bind:value={selectedLandingPage}
              placeholder="Select a landing page..."
            />
            <input
              type="hidden"
              name="defaultLandingPage"
              value={selectedLandingPage ?? ''}
            />
          </SettingItem>
        </div>
      </section>

      {#if data.licensingEnabled}
        <section class="space-y-1">
          <div class="flex items-center justify-between gap-4 pb-3">
            <h2
              class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
            >
              Image Licensing
            </h2>
          </div>
          <div
            class="divide-y divide-gray-100 dark:divide-gray-800 rounded-xl bg-gray-50/50 dark:bg-gray-900/30 border border-gray-100 dark:border-gray-800 overflow-hidden"
          >
            <SettingItem>
              {#snippet label()}
                Default License
              {/snippet}
              {#snippet hint()}
                This license will be automatically applied to new uploads
              {/snippet}
              {#snippet footer()}
                {#if selectedLicenseInfo}
                  <Notice
                    variant="info"
                    appearance="subtle"
                    size="sm"
                    class="px-4"
                  >
                    <div class="flex gap-3">
                      <Icon icon="ph:scales" class="w-4 h-4 shrink-0 mt-0.5" />
                      <div class="space-y-1">
                        <p class="font-medium">{selectedLicenseInfo.title}</p>
                        <p class="text-xs opacity-75">
                          {selectedLicenseInfo.description}
                        </p>
                        {#if selectedLicenseInfo.url}
                          <a
                            href={selectedLicenseInfo.url}
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-1 text-xs hover:underline"
                          >
                            <span>Learn more</span>
                            <Icon
                              icon="heroicons:arrow-top-right-on-square"
                              class="w-3 h-3"
                            />
                          </a>
                        {/if}
                      </div>
                    </div>
                  </Notice>
                {/if}
              {/snippet}
              <Select
                items={licenseOptions}
                bind:value={selectedLicense}
                placeholder="Select a license..."
              />
              <input
                type="hidden"
                name="defaultLicense"
                value={selectedLicense ?? ''}
              />
            </SettingItem>

            <SettingItem>
              {#snippet label()}
                Sync to existing images
              {/snippet}
              {#snippet hint()}
                Apply this license to all your existing images
              {/snippet}
              <Switch
                id="syncLicenseToImages"
                name="syncLicenseToImages"
                bind:checked={syncToImages}
              />
            </SettingItem>
          </div>
        </section>
      {/if}
    </div>

    <div class="flex items-center justify-end gap-3 pt-4">
      {#if $isPreferencesFormLoading}
        <div
          class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400"
        >
          <Loader variant="minimal" size="xs" />
          <span>Saving...</span>
        </div>
      {/if}

      <Button
        type="submit"
        variant="soft-blue"
        rounded="full"
        size="sm"
        disabled={$isPreferencesFormLoading}
      >
        Save Changes
      </Button>
    </div>
  </form>
</div>
