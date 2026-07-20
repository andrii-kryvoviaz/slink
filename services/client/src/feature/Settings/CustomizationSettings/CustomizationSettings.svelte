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
        URL to a custom logo image (leave empty for default)
      {/snippet}
      <Input
        name="customizationLogoUrl"
        bind:value={settings.logoUrl}
        placeholder="https://example.com/logo.png"
        variant="modern"
        size="md"
        rounded="lg"
        error={errors['customization.logoUrl']}
      />
    </SettingItem>
  {/snippet}
</SettingsPane>
