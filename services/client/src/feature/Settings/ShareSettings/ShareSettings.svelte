<script lang="ts">
  import { SettingItem, SettingsPane } from '@slink/feature/Settings';
  import { Switch } from '@slink/ui/components/switch';

  import { t } from '$lib/i18n';

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
    {$t('settings.share.title')}
  {/snippet}
  {#snippet description()}
    {$t('settings.share.description')}
  {/snippet}

  <SettingItem
    defaultValue={defaultSettings?.enableUrlShortening}
    currentValue={settings.enableUrlShortening}
    reset={(value) => {
      settings.enableUrlShortening = value;
    }}
  >
    {#snippet label()}
      {$t('settings.share.url_shortening.label')}
    {/snippet}
    {#snippet hint()}
      {$t('settings.share.url_shortening.hint')}
    {/snippet}
    <Switch
      name="shareEnableUrlShortening"
      bind:checked={settings.enableUrlShortening}
    />
  </SettingItem>
</SettingsPane>
