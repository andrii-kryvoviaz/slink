<script lang="ts">
  import { Loader } from '@slink/feature/Layout';
  import { Notice } from '@slink/feature/Text';
  import { Select } from '@slink/ui/components';
  import { Button } from '@slink/ui/components/button';
  import { Switch } from '@slink/ui/components/switch';

  import { enhance } from '$app/forms';
  import Icon from '@iconify/svelte';
  import { fade } from 'svelte/transition';

  import type { User } from '@slink/lib/auth/Type/User';

  import { withLoadingState } from '@slink/utils/form/withLoadingState';
  import { useWritable } from '@slink/utils/store/contextAwareStore';
  import { toast } from '@slink/utils/ui/toast-sonner.svelte';

  interface License {
    id: string;
    title: string;
    name: string;
    description: string;
    url: string | null;
  }

  interface Preferences {
    defaultLicense: string | null;
  }

  interface PageData {
    user: User;
    preferences: Preferences;
    licenses: License[];
  }

  interface Props {
    data: PageData;
    form: any;
  }

  let { data, form }: Props = $props();

  let licenses = $derived(data.licenses);
  let selectedLicense = $state(data.preferences?.defaultLicense ?? '');
  let syncToImages = $state(false);

  let selectedLicenseInfo = $derived(
    licenses.find((l) => l.id === selectedLicense),
  );

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

<div class="min-h-full">
  <div
    class="mx-auto max-w-2xl px-4 py-8 sm:px-6 lg:px-8"
    in:fade={{ duration: 400 }}
  >
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-slate-900 dark:text-slate-100">
        Preferences
      </h1>
      <p class="mt-2 text-slate-600 dark:text-slate-400">
        Configure your default settings and preferences
      </p>
    </div>

    <div
      class="rounded-2xl bg-white p-6 shadow-sm dark:bg-slate-800/50 border border-slate-200/50 dark:border-slate-700/50"
    >
      <div class="mb-6">
        <h3
          class="text-lg font-semibold text-slate-900 dark:text-slate-100 flex items-center gap-2"
        >
          <Icon
            icon="ph:scales"
            class="h-5 w-5 text-slate-500 dark:text-slate-400"
          />
          Image Licensing
        </h3>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
          Configure default license settings for your uploaded images
        </p>
      </div>

      <form
        action="?/updatePreferences"
        method="POST"
        use:enhance={withLoadingState(isPreferencesFormLoading)}
        class="space-y-4"
      >
        <div class="space-y-2">
          <label
            for="defaultLicense"
            class="block text-sm font-medium text-slate-700 dark:text-slate-300"
          >
            Default License
          </label>
          <p class="text-xs text-slate-500 dark:text-slate-400 mb-2">
            This license will be automatically applied to new uploads
          </p>
          <Select
            class="w-full max-w-md"
            items={licenseOptions}
            bind:value={selectedLicense}
            placeholder="Select a license..."
          />
          <input
            type="hidden"
            name="defaultLicense"
            value={selectedLicense ?? ''}
          />
          {#if selectedLicenseInfo}
            <Notice variant="info" size="sm" class="mt-4 rounded-lg">
              <div class="flex gap-3">
                <Icon icon="ph:scales" class="w-5 h-5 shrink-0 mt-0.5" />
                <div class="space-y-2">
                  <div>
                    <p class="font-semibold">{selectedLicenseInfo.title}</p>
                    <p class="text-xs opacity-75">{selectedLicenseInfo.name}</p>
                  </div>
                  <p class="leading-relaxed">
                    {selectedLicenseInfo.description}
                  </p>
                  {#if selectedLicenseInfo.url}
                    <a
                      href={selectedLicenseInfo.url}
                      target="_blank"
                      rel="noopener noreferrer"
                      class="inline-flex items-center gap-1.5 font-medium hover:underline"
                    >
                      <span>Learn more</span>
                      <Icon
                        icon="heroicons:arrow-top-right-on-square"
                        class="w-3.5 h-3.5"
                      />
                    </a>
                  {/if}
                </div>
              </div>
            </Notice>
          {/if}
        </div>

        <div class="flex items-center justify-between pt-2">
          <label
            for="syncLicenseToImages"
            class="text-sm text-slate-600 dark:text-slate-400"
          >
            Apply this license to all my existing images
          </label>
          <Switch
            id="syncLicenseToImages"
            name="syncLicenseToImages"
            bind:checked={syncToImages}
          />
        </div>

        <div class="flex justify-end pt-4">
          <Button
            variant="outline"
            size="sm"
            type="submit"
            loading={$isPreferencesFormLoading}
            class="min-w-[140px] bg-slate-900 hover:bg-slate-800 text-white border-slate-900 hover:border-slate-800 dark:bg-slate-100 dark:hover:bg-slate-200 dark:text-slate-900 dark:border-slate-100 dark:hover:border-slate-200"
          >
            {#if $isPreferencesFormLoading}
              <Loader
                variant="simple"
                size="xs"
                class="mr-2 border-white/50! border-t-white!"
              />
              Saving...
            {:else}
              <Icon icon="ph:check" class="h-4 w-4 mr-2" />
              Save Preferences
            {/if}
          </Button>
        </div>
      </form>
    </div>
  </div>
</div>
