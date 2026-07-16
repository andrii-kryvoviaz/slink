<script lang="ts">
  import { SettingItem, SettingsPane } from '@slink/feature/Settings';
  import { Input } from '@slink/ui/components/input';

  import type { CustomizationSettings as CustomizationSettingsType } from '@slink/lib/settings/Type/CustomizationSettings';
  import type { SettingCategory } from '@slink/lib/settings/Type/GlobalSettings';

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
</script>

<SettingsPane category="customization" {loading} on={{ save: onSave }}>
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
        The name displayed in the browser tab, sidebar, and navigation
      {/snippet}
      <Input
        name="customizationSiteName"
        bind:value={settings.siteName}
        placeholder="Slink"
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
        A short description shown in the browser tab and meta tags
      {/snippet}
      <Input
        name="customizationSiteDescription"
        bind:value={settings.siteDescription}
        placeholder="Fast and secure image sharing service"
        variant="modern"
        size="md"
        rounded="lg"
        error={errors['customization.siteDescription']}
      />
    </SettingItem>

    <SettingItem
      layout="stacked"
      defaultValue={defaultSettings?.faviconUrl}
      currentValue={settings.faviconUrl}
      reset={(value) => {
        settings.faviconUrl = value;
      }}
    >
      {#snippet label()}
        Favicon URL
      {/snippet}
      {#snippet hint()}
        URL to a custom favicon image (leave empty for default)
      {/snippet}
      <Input
        name="customizationFaviconUrl"
        bind:value={settings.faviconUrl}
        placeholder="https://example.com/favicon.png"
        variant="modern"
        size="md"
        rounded="lg"
        error={errors['customization.faviconUrl']}
      />
    </SettingItem>

    <SettingItem
      layout="stacked"
      defaultValue={defaultSettings?.customCss}
      currentValue={settings.customCss}
      reset={(value) => {
        settings.customCss = value;
      }}
    >
      {#snippet label()}
        Custom CSS
      {/snippet}
      {#snippet hint()}
        Override the application CSS entirely. Use standard CSS syntax. Leave
        empty for default styling.
      {/snippet}
      <textarea
        name="customizationCustomCss"
        bind:value={settings.customCss}
        placeholder="/* Your custom CSS rules here */"
        class="bg-bg-secondary border border-bc-input rounded-lg px-4 py-3 text-sm text-text-primary placeholder:text-text-secondary/50 w-full min-h-[200px] font-mono resize-y focus:outline-none focus:ring-2 focus:ring-accent"
        class:border-destructive={errors['customization.customCss']}></textarea>
      {#if errors['customization.customCss']}
        <p class="text-destructive text-xs mt-1">
          {errors['customization.customCss']}
        </p>
      {/if}
    </SettingItem>
  {/snippet}
</SettingsPane>
