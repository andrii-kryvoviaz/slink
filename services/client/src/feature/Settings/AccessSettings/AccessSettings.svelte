<script lang="ts">
  import { SettingItem, SettingsPane } from '@slink/feature/Settings';
  import { Switch } from '@slink/ui/components/switch';

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
    Access Control
  {/snippet}
  {#snippet description()}
    Configure guest access and upload permissions
  {/snippet}

  <SettingItem
    defaultValue={defaultSettings?.allowGuestUploads}
    currentValue={settings.allowGuestUploads}
    reset={(value) => {
      settings.allowGuestUploads = value;
    }}
  >
    {#snippet label()}
      Guest Upload
    {/snippet}
    {#snippet hint()}
      Allow unauthenticated users to upload images without creating an account.
      When enabled without Guest Access, users will see a success message but
      cannot browse uploaded images
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
      Guest Access (View-Only)
    {/snippet}
    {#snippet hint()}
      Allow unauthenticated users to view and browse images
    {/snippet}
    <Switch
      name="accessAllowUnauthenticatedAccess"
      bind:checked={settings.allowUnauthenticatedAccess}
    />
  </SettingItem>
</SettingsPane>
