<script lang="ts">
  import { getLicenseLabels } from '@slink/feature/Image';
  import { Loader, ThemePicker, ThemePreview } from '@slink/feature/Layout';
  import { SettingItem, SettingsSection } from '@slink/feature/Settings';
  import { Notice, Subtitle, Title } from '@slink/feature/Text';
  import { Select } from '@slink/ui/components';
  import { Button } from '@slink/ui/components/button';
  import { Switch } from '@slink/ui/components/switch';

  import { enhance } from '$app/forms';
  import { page } from '$app/state';
  import Icon from '@iconify/svelte';
  import { fade } from 'svelte/transition';

  import type { UserPreferencesResponse } from '@slink/api/Response';

  import type { User } from '@slink/lib/auth/Type/User';
  import { LandingPage } from '@slink/lib/enum/LandingPage';
  import type { License } from '@slink/lib/enum/License';
  import { Locale, resolveTheme } from '@slink/lib/settings/Settings.enums';
  import type { UploadPolicy } from '@slink/lib/settings/UploadPolicy';
  import { themes } from '@slink/lib/settings/themes.svelte';
  import { applyLocale } from '@slink/lib/utils/i18n';
  import { messages } from '@slink/lib/utils/i18n/messages/toast.language';

  import { withLoadingState } from '@slink/utils/form/withLoadingState';
  import { useWritable } from '@slink/utils/store/contextAwareStore';
  import { toast } from '@slink/utils/ui/toast-sonner.svelte';

  import { PreferencesPageState } from './PreferencesPageState.svelte';

  interface PageData {
    user: User;
    preferences: UserPreferencesResponse | null;
    licenses: License[];
    licensingEnabled: boolean;
    uploadPolicy: UploadPolicy;
  }

  interface Props {
    data: PageData;
  }

  let { data }: Props = $props();

  const { settings } = page.data;
  const formState = new PreferencesPageState(data.preferences);

  formState.onChanged('locale', (locale) =>
    applyLocale(locale as Locale, settings),
  );

  let licenses = $derived(data.licenses);

  let selectedLicenseInfo = $derived(
    licenses.find((l) => l.id === formState.license),
  );

  const visibilityOptions = [
    { value: 'public', label: 'Public' },
    { value: 'private', label: 'Private' },
  ];

  const exifPreferenceOptions = [
    { value: 'default', label: 'Use server default' },
    { value: 'strip', label: 'Always strip' },
    { value: 'keep', label: 'Always keep' },
  ];

  const landingPageOptions = [
    { value: LandingPage.Explore, label: 'Explore' },
    { value: LandingPage.Upload, label: 'Upload' },
  ];

  const localeOptions = [
    { value: Locale.EN, label: 'English' },
    { value: Locale.DE, label: 'Deutsch' },
    { value: Locale.ES, label: 'Español' },
    { value: Locale.FR, label: 'Français' },
    { value: Locale.IT, label: 'Italiano' },
    { value: Locale.PL, label: 'Polski' },
    { value: Locale.UK, label: 'Українська' },
    { value: Locale.JA, label: '日本語' },
    { value: Locale.ZH, label: '中文' },
  ];

  let isPreferencesFormLoading = useWritable(
    'updatePreferencesFormLoadingState',
    false,
  );

  let formError = $state<string | null>(null);

  const licenseOptions = $derived(
    licenses.map((license) => ({
      value: license.id,
      label: getLicenseLabels(license.id).title,
    })),
  );

  const selectedLicenseLabels = $derived(
    selectedLicenseInfo ? getLicenseLabels(selectedLicenseInfo.id) : null,
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
    <Title size="sm">Preferences</Title>
    <Subtitle>Configure your default settings and preferences</Subtitle>
  </header>

  <form
    action="?/updatePreferences"
    method="POST"
    use:enhance={withLoadingState(isPreferencesFormLoading, {
      reset: false,
      onSubmit: () => {
        formError = null;
      },
      onSuccess: async () => {
        await formState.commit();
        settings.theme.current = resolveTheme(formState.theme);
        toast.success(messages.preferences.updated);
      },
      onError: (data) => {
        const errors = data?.errors as Record<string, string> | undefined;
        formError = errors?.message ?? messages.general.somethingWentWrong;
      },
    })}
  >
    {#if formError}
      <Notice variant="error" class="mb-5">
        {formError}
      </Notice>
    {/if}
    <div class="space-y-8">
      <SettingsSection>
        {#snippet title()}
          Language
        {/snippet}
        <SettingItem>
          {#snippet label()}
            Display Language
          {/snippet}
          {#snippet hint()}
            Choose your preferred language for the interface
          {/snippet}
          <Select
            items={localeOptions}
            bind:value={formState.locale}
            name="display.language"
          />
        </SettingItem>
      </SettingsSection>

      <SettingsSection id="appearance" class="scroll-mt-20">
        {#snippet title()}
          Appearance
        {/snippet}
        <SettingItem>
          {#snippet label()}
            Theme
          {/snippet}
          {#snippet hint()}
            Choose your preferred color theme for the interface
          {/snippet}
          {#snippet footer()}
            <div class="px-4 pb-4">
              <ThemePreview theme={formState.theme} />
            </div>
          {/snippet}
          <ThemePicker
            {themes}
            bind:value={formState.theme}
            name="display.theme"
          />
        </SettingItem>
      </SettingsSection>

      <SettingsSection>
        {#snippet title()}
          Navigation
        {/snippet}
        <SettingItem>
          {#snippet label()}
            Default Landing Page
          {/snippet}
          {#snippet hint()}
            The page to show when you visit the site
          {/snippet}
          <Select
            items={landingPageOptions}
            bind:value={formState.landingPage}
            placeholder="Select a landing page..."
            name="navigation.landingPage"
          />
        </SettingItem>
      </SettingsSection>

      <SettingsSection>
        {#snippet title()}
          Image Uploads
        {/snippet}
        {#if !data.uploadPolicy.allowOnlyPublicImages}
          <SettingItem>
            {#snippet label()}
              Default Visibility
            {/snippet}
            {#snippet hint()}
              New uploads will be set to public or private by default
            {/snippet}
            <Select
              items={visibilityOptions}
              bind:value={formState.visibility}
              placeholder="Select visibility..."
              name="image.defaultVisibility"
            />
          </SettingItem>
        {/if}

        <SettingItem>
          {#snippet label()}
            EXIF Metadata
          {/snippet}
          {#snippet hint()}
            Override how metadata is stripped from your uploads.
          {/snippet}
          <Select
            items={exifPreferenceOptions}
            bind:value={formState.exifPreference}
            placeholder="Select metadata handling..."
            name="image.stripExifMetadataOverride"
          />
        </SettingItem>

        <SettingItem>
          {#snippet label()}
            Auto-publish API uploads
          {/snippet}
          {#snippet hint()}
            Make uploads from API tools (e.g. ShareX) immediately shareable.
          {/snippet}
          <Switch
            name="image.externalUploadAutoPublish"
            bind:checked={formState.externalUploadAutoPublish}
          />
        </SettingItem>
      </SettingsSection>

      {#if data.licensingEnabled}
        <SettingsSection>
          {#snippet title()}
            Image Licensing
          {/snippet}
          <SettingItem>
            {#snippet label()}
              Default License
            {/snippet}
            {#snippet hint()}
              This license will be automatically applied to new uploads
            {/snippet}
            {#snippet footer()}
              {#if selectedLicenseInfo && selectedLicenseLabels}
                <Notice
                  variant="info"
                  appearance="subtle"
                  size="sm"
                  class="px-4"
                >
                  <div class="flex gap-3">
                    <Icon icon="ph:scales" class="w-4 h-4 shrink-0 mt-0.5" />
                    <div class="space-y-1">
                      <p class="font-medium">{selectedLicenseLabels.title}</p>
                      <p class="text-xs opacity-75">
                        {selectedLicenseLabels.description}
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
              bind:value={formState.license}
              placeholder="Select a license..."
              name="license.default"
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
              name="license.syncToImages"
              bind:checked={formState.syncToImages}
            />
          </SettingItem>
        </SettingsSection>
      {/if}
    </div>

    <div class="flex items-center justify-end gap-3 pt-4">
      {#if $isPreferencesFormLoading}
        <div class="flex items-center gap-2 text-sm text-foreground-muted">
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
