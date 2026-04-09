<script lang="ts">
  import { SettingItem, SettingsPane } from '@slink/feature/Settings';
  import { Select } from '@slink/ui/components';
  import { NumberInput } from '@slink/ui/components/input';
  import { Switch } from '@slink/ui/components/switch';

  import { t } from '$lib/i18n';

  import type { SettingCategory } from '@slink/lib/settings/Type/GlobalSettings';
  import type { UserSettings as UserSettingsType } from '@slink/lib/settings/Type/UserSettings';

  interface Props {
    settings: UserSettingsType;
    defaultSettings?: UserSettingsType;
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

<SettingsPane category="user" {loading} on={{ save: onSave }}>
  {#snippet title()}
    {$t('settings.user.title')}
  {/snippet}
  {#snippet description()}
    {$t('settings.user.description')}
  {/snippet}

  <SettingItem
    defaultValue={defaultSettings?.allowRegistration}
    currentValue={settings.allowRegistration}
    reset={(value) => {
      settings.allowRegistration = value;
    }}
  >
    {#snippet label()}
      {$t('settings.user.registration.label')}
    {/snippet}
    {#snippet hint()}
      {$t('settings.user.registration.hint')}
    {/snippet}
    <Switch
      name="allowRegistration"
      bind:checked={settings.allowRegistration}
    />
  </SettingItem>

  {#if settings.allowRegistration}
    <SettingItem
      defaultValue={defaultSettings?.approvalRequired}
      currentValue={settings.approvalRequired}
      reset={(value) => {
        settings.approvalRequired = value;
      }}
    >
      {#snippet label()}
        {$t('settings.user.approval_required.label')}
      {/snippet}
      {#snippet hint()}
        {$t('settings.user.approval_required.hint')}
      {/snippet}
      <Switch
        name="approvalRequired"
        bind:checked={settings.approvalRequired}
      />
    </SettingItem>

    <SettingItem
      defaultValue={defaultSettings?.password.minLength}
      currentValue={settings.password.minLength}
      reset={(value) => {
        settings.password.minLength = value;
      }}
    >
      {#snippet label()}
        {$t('settings.user.password_min_length.label')}
      {/snippet}
      {#snippet hint()}
        {$t('settings.user.password_min_length.hint')}
      {/snippet}
      <NumberInput
        name="passwordLength"
        min={6}
        bind:value={settings.password.minLength}
        variant="input"
        size="md"
      />
    </SettingItem>

    <SettingItem
      defaultValue={defaultSettings?.password.requirements}
      currentValue={settings.password.requirements}
      reset={(value) => {
        settings.password.requirements = value;
      }}
    >
      {#snippet label()}
        {$t('settings.user.password_requirements.label')}
      {/snippet}
      {#snippet hint()}
        {$t('settings.user.password_requirements.hint')}
      {/snippet}
      <Select
        type="bitmask"
        class="w-full max-w-md"
        items={[
          {
            value: '1',
            label: $t('settings.user.password_requirements.options.numbers'),
            icon: 'ph:number-nine-thin',
          },
          {
            value: '2',
            label: $t('settings.user.password_requirements.options.lowercase'),
            icon: 'material-symbols-light:lowercase-rounded',
          },
          {
            value: '4',
            label: $t('settings.user.password_requirements.options.uppercase'),
            icon: 'material-symbols-light:uppercase-rounded',
          },
          {
            value: '8',
            label: $t('settings.user.password_requirements.options.special'),
            icon: 'material-symbols-light:asterisk-rounded',
          },
        ]}
        bind:value={settings.password.requirements}
      />
    </SettingItem>
  {/if}
</SettingsPane>
