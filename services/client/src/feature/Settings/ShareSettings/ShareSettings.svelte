<script lang="ts">
  import { SettingItem, SettingsPane } from '@slink/feature/Settings';
  import { NumberInput } from '@slink/ui/components/input';
  import { Switch } from '@slink/ui/components/switch';

  import type { SettingCategory } from '@slink/lib/settings/Type/GlobalSettings';
  import type { ShareSettings as ShareSettingsType } from '@slink/lib/settings/Type/ShareSettings';

  interface Props {
    settings: ShareSettingsType;
    defaultSettings?: ShareSettingsType;
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

<SettingsPane category="share" {loading} on={{ save: onSave }}>
  {#snippet title()}
    Share
  {/snippet}
  {#snippet description()}
    Configure URL sharing behavior
  {/snippet}

  <SettingItem
    defaultValue={defaultSettings?.enableUrlShortening}
    currentValue={settings.enableUrlShortening}
    reset={(value) => {
      settings.enableUrlShortening = value;
    }}
  >
    {#snippet label()}
      URL Shortening
    {/snippet}
    {#snippet hint()}
      Generate short URLs when copying image links
    {/snippet}
    <Switch
      name="shareEnableUrlShortening"
      bind:checked={settings.enableUrlShortening}
    />
  </SettingItem>

  {#if settings.enableUrlShortening}
    <SettingItem
      defaultValue={defaultSettings?.shortUrlLength}
      currentValue={settings.shortUrlLength}
      reset={(value) => {
        settings.shortUrlLength = value;
      }}
    >
      {#snippet label()}
        Short URL Length
      {/snippet}
      {#snippet hint()}
        Number of characters in generated short codes (4 to 32)
      {/snippet}
      <NumberInput
        name="shareShortUrlLength"
        min={4}
        max={32}
        bind:value={settings.shortUrlLength}
        variant="input"
        size="md"
      />
    </SettingItem>
  {/if}
</SettingsPane>
