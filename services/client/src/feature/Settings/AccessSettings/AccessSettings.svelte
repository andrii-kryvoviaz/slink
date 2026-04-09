<script lang="ts">
  import { SettingItem, SettingsPane } from '@slink/feature/Settings';
  import { Switch } from '@slink/ui/components/switch';

  import { t } from '$lib/i18n';

  import type { AccessSettings as AccessSettingsType } from '@slink/lib/settings/Type/AccessSettings';
  import type { SettingCategory } from '@slink/lib/settings/Type/GlobalSettings';

  interface Props {
    settings: AccessSettingsType;
    defaultSettings?: AccessSettingsType;
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

<SettingsPane category="access" {loading} on={{ save: onSave }}>
  {#snippet title()}
    {$t('settings.access.title')}
  {/snippet}
  {#snippet description()}
    {$t('settings.access.description')}
  {/snippet}

  <SettingItem
    defaultValue={defaultSettings?.allowGuestUploads}
    currentValue={settings.allowGuestUploads}
    reset={(value) => {
      settings.allowGuestUploads = value;
    }}
  >
    {#snippet label()}
      {$t('settings.access.guest_upload.label')}
    {/snippet}
    {#snippet hint()}
      {$t('settings.access.guest_upload.hint')}
    {/snippet}
    <Switch
      name="accessAllowGuestUploads"
      bind:checked={settings.allowGuestUploads}
    />
  </SettingItem>

  <SettingItem
    defaultValue={defaultSettings?.allowUnauthenticatedAccess}
    currentValue={settings.allowUnauthenticatedAccess}
    reset={(value) => {
      settings.allowUnauthenticatedAccess = value;
    }}
  >
    {#snippet label()}
      {$t('settings.access.guest_access.label')}
    {/snippet}
    {#snippet hint()}
      {$t('settings.access.guest_access.hint')}
    {/snippet}
    <Switch
      name="accessAllowUnauthenticatedAccess"
      bind:checked={settings.allowUnauthenticatedAccess}
    />
  </SettingItem>
</SettingsPane>
