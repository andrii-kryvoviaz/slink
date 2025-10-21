<script lang="ts">
  import { SettingItem, SettingsPane } from '@slink/feature/Settings';
  import { Select } from '@slink/ui/components';
  import { NumberInput } from '@slink/ui/components/input';
  import { Switch } from '@slink/ui/components/switch';

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
    User Management
  {/snippet}
  {#snippet description()}
    Control user registration, authentication, and security requirements
  {/snippet}

  <SettingItem
    defaultValue={defaultSettings?.allowRegistration}
    currentValue={settings.allowRegistration}
    reset={(value) => {
      settings.allowRegistration = value;
    }}
  >
    {#snippet label()}
      User Registration
    {/snippet}
    {#snippet hint()}
      Allow new users to create accounts
    {/snippet}
    <div class="flex justify-end">
      <Switch
        name="allowRegistration"
        bind:checked={settings.allowRegistration}
      />
    </div>
  </SettingItem>

  {#if settings.allowRegistration}
    <div class="space-y-6">
      <SettingItem
        defaultValue={defaultSettings?.approvalRequired}
        currentValue={settings.approvalRequired}
        reset={(value) => {
          settings.approvalRequired = value;
        }}
      >
        {#snippet label()}
          Require Admin Approval
        {/snippet}
        {#snippet hint()}
          New users must be approved by an administrator before accessing the
          application
        {/snippet}
        <div class="flex justify-end">
          <Switch
            name="approvalRequired"
            bind:checked={settings.approvalRequired}
          />
        </div>
      </SettingItem>

      <SettingItem
        defaultValue={defaultSettings?.password.minLength}
        currentValue={settings.password.minLength}
        reset={(value) => {
          settings.password.minLength = value;
        }}
      >
        {#snippet label()}
          Minimum Password Length
        {/snippet}
        {#snippet hint()}
          Required minimum number of characters for user passwords
        {/snippet}
        <NumberInput
          name="passwordLength"
          min={6}
          bind:value={settings.password.minLength}
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
          Password Requirements
        {/snippet}
        {#snippet hint()}
          Character types required in user passwords for enhanced security
        {/snippet}
        <Select
          type="bitmask"
          class="w-full max-w-md"
          items={[
            {
              value: '1',
              label: 'Numbers (0-9)',
              icon: 'ph:number-nine-thin',
            },
            {
              value: '2',
              label: 'Lowercase Letters (a-z)',
              icon: 'material-symbols-light:lowercase-rounded',
            },
            {
              value: '4',
              label: 'Uppercase Letters (A-Z)',
              icon: 'material-symbols-light:uppercase-rounded',
            },
            {
              value: '8',
              label: 'Special Characters (!@#$)',
              icon: 'material-symbols-light:asterisk-rounded',
            },
          ]}
          bind:value={settings.password.requirements}
        />
      </SettingItem>
    </div>
  {/if}
</SettingsPane>
