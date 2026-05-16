<script lang="ts">
  import { SettingItem, SettingsPane } from '@slink/feature/Settings';
  import { Notice } from '@slink/feature/Text';
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
    {/snippet}
    <Switch
      name="accessAllowGuestUploads"
      bind:checked={settings.allowGuestUploads}
    />

    {#snippet footer()}
      {#if settings.allowGuestUploads}
        <Notice variant="warning" appearance="subtle" size="sm" class="px-4">
          Anyone can upload without identifying themselves. This can lead to
          uncontrolled storage growth and exposure to illegal or abusive
          content.
        </Notice>
      {/if}
    {/snippet}
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
      Allow unauthenticated users to browse the public gallery and open images
      marked as public.
    {/snippet}
    <Switch
      name="accessAllowUnauthenticatedAccess"
      bind:checked={settings.allowUnauthenticatedAccess}
    />
  </SettingItem>

  <SettingItem
    defaultValue={defaultSettings?.requireAuthForMediaShares}
    currentValue={settings.requireAuthForMediaShares}
    reset={(value) => {
      settings.requireAuthForMediaShares = value;
    }}
  >
    {#snippet label()}
      Media Share Access
    {/snippet}
    {#snippet hint()}
      Require users to log in before opening shared media.
    {/snippet}
    <Switch
      name="accessRequireAuthForMediaShares"
      bind:checked={settings.requireAuthForMediaShares}
    />

    {#snippet footer()}
      <Notice variant="info" appearance="subtle" size="sm" class="px-4">
        Keep this off if you intend to embed media on external sites.
      </Notice>
    {/snippet}
  </SettingItem>

  <SettingItem
    defaultValue={defaultSettings?.requireAuthForCollectionShares}
    currentValue={settings.requireAuthForCollectionShares}
    reset={(value) => {
      settings.requireAuthForCollectionShares = value;
    }}
  >
    {#snippet label()}
      Collection Share Access
    {/snippet}
    {#snippet hint()}
      Require users to log in before opening shared collections.
    {/snippet}
    <Switch
      name="accessRequireAuthForCollectionShares"
      bind:checked={settings.requireAuthForCollectionShares}
    />
  </SettingItem>
</SettingsPane>
