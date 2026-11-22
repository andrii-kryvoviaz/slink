<script lang="ts">
  import {
    SettingItem,
    SettingsPane,
    ThemeSettings,
  } from '@slink/feature/Settings';
  import { FilePickerButton } from '@slink/ui/components/file-picker-button';
  import { Input } from '@slink/ui/components/input';

  import Icon from '@iconify/svelte';

  import type { CustomizationSettings as CustomizationSettingsType } from '@slink/lib/settings/Type/CustomizationSettings';
  import type { SettingCategory } from '@slink/lib/settings/Type/GlobalSettings';

  import { LogoUpload } from './LogoUpload.svelte';

  interface Props {
    settings: CustomizationSettingsType;
    defaultSettings?: CustomizationSettingsType;
    loading?: boolean;
    onSave: (event: {
      category: SettingCategory;
      data: Record<string, string | File>;
    }) => void;
  }

  let {
    settings = $bindable(),
    defaultSettings,
    loading = false,
    onSave,
  }: Props = $props();

  const defaultLogoUrl = '/favicon.svg';

  const logoUpload = new LogoUpload(settings.logoUrl || defaultLogoUrl);

  const handlePick = async (file: File) => {
    const url = await logoUpload.upload(file);

    if (url) {
      settings.logoUrl = url;
    }
  };

  $effect(() => {
    logoUpload.schedulePreview(settings.logoUrl || defaultLogoUrl);
  });
</script>

<SettingsPane category="customization" {loading} on={{ save: onSave }}>
  {#snippet title()}
    Customization
  {/snippet}
  {#snippet description()}
    Name, brand and theme your Slink instance.
  {/snippet}

  {#snippet children(errors)}
    <SettingItem
      layout="stacked"
      defaultValue={defaultSettings?.siteName}
      currentValue={settings.siteName}
      reset={(value) => {
        settings.siteName = value;
      }}
    >
      {#snippet label()}
        Site Name
      {/snippet}
      {#snippet hint()}
        The public name of your instance.
      {/snippet}
      <Input
        name="customizationSiteName"
        bind:value={settings.siteName}
        placeholder="Slink"
        maxlength={64}
        variant="modern"
        size="md"
        rounded="lg"
        error={errors['customization.siteName']}
      />
    </SettingItem>

    <SettingItem
      layout="stacked"
      defaultValue={defaultSettings?.siteDescription}
      currentValue={settings.siteDescription}
      reset={(value) => {
        settings.siteDescription = value;
      }}
    >
      {#snippet label()}
        Site Description
      {/snippet}
      {#snippet hint()}
        A short tagline describing your instance.
      {/snippet}
      <Input
        name="customizationSiteDescription"
        bind:value={settings.siteDescription}
        placeholder="Fast and secure image sharing service"
        maxlength={255}
        variant="modern"
        size="md"
        rounded="lg"
        error={errors['customization.siteDescription']}
      />
    </SettingItem>

    <SettingItem
      layout="stacked"
      defaultValue={defaultSettings?.logoUrl}
      currentValue={settings.logoUrl}
      reset={(value) => {
        settings.logoUrl = value;
      }}
    >
      {#snippet label()}
        Logo URL
      {/snippet}
      {#snippet hint()}
        Link to your own logo image. Leave empty to use the default.
      {/snippet}
      <div class="flex items-start gap-3">
        <div class="flex-1">
          <Input
            name="customizationLogoUrl"
            inputmode="url"
            bind:value={settings.logoUrl}
            placeholder="https://example.com/logo.png"
            variant="modern"
            size="md"
            rounded="lg"
            class="pr-24"
            error={logoUpload.error || errors['customization.logoUrl']}
          >
            <div class="absolute inset-y-0 right-1 flex items-center">
              <FilePickerButton
                variant="ghost"
                size="xs"
                rounded="md"
                accept="image/png,image/jpeg,image/webp,image/gif,image/svg+xml"
                disabled={logoUpload.uploading}
                onPick={handlePick}
              >
                <Icon icon="ph:upload-simple" class="h-4 w-4" />
                Upload
              </FilePickerButton>
            </div>
          </Input>
        </div>
        {#if logoUpload.previewError && !logoUpload.showLoader}
          <div
            class="flex h-9 shrink-0 items-center gap-1.5 rounded-lg border border-border bg-background px-3 text-xs text-foreground-muted"
          >
            <Icon icon="ph:image-broken" class="h-4 w-4" />
            <span>Couldn't load this image</span>
          </div>
        {:else}
          <div class="relative h-9 w-9 shrink-0">
            <img
              src={logoUpload.previewUrl}
              alt="Logo preview"
              class="h-9 w-9 rounded-lg border border-border bg-background object-contain p-1"
              onload={() => logoUpload.onPreviewLoaded()}
              onerror={() => logoUpload.onPreviewError()}
            />
            {#if logoUpload.showLoader}
              <div
                class="absolute inset-0 flex items-center justify-center rounded-lg border border-border bg-background"
              >
                <Icon
                  icon="lucide:loader-2"
                  class="h-4 w-4 animate-spin text-foreground-muted"
                />
              </div>
            {/if}
          </div>
        {/if}
      </div>
    </SettingItem>

    <ThemeSettings bind:settings {defaultSettings} {errors} />

    <SettingItem layout="stacked">
      {#snippet label()}
        Custom CSS
      {/snippet}
      {#snippet hint()}
        Applied on top of the theme for every visitor. If it breaks a page, add
        <code class="font-mono">?custom-css=off</code> to the address to load it without
        custom CSS.
      {/snippet}
      <textarea
        name="customizationCustomCss"
        aria-label="Custom CSS"
        bind:value={settings.customCss}
        placeholder=":root &#123; --radius: 0; &#125;"
        maxlength={65536}
        rows={8}
        spellcheck="false"
        class="border-border bg-background dark:bg-input/30 placeholder:text-foreground-muted shadow-xs flex w-full min-w-0 resize-y rounded-lg border px-4 py-2.5 font-mono text-sm outline-none transition-[color,box-shadow] focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] aria-invalid:border-danger"
        aria-invalid={errors['customization.customCss'] ? true : undefined}
      ></textarea>
      {#if errors['customization.customCss']}
        <p class="mt-1.5 text-xs text-accent-strong">
          {errors['customization.customCss']}
        </p>
      {/if}
    </SettingItem>
  {/snippet}
</SettingsPane>
