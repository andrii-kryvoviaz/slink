<script lang="ts">
  import { SettingItem, SettingsPane } from '@slink/feature/Settings';
  import { Notice } from '@slink/feature/Text';
  import { Select } from '@slink/ui/components';
  import { Input } from '@slink/ui/components/input';
  import { Switch } from '@slink/ui/components/switch';

  import { t } from '$lib/i18n';

  import type { SettingCategory } from '@slink/lib/settings/Type/GlobalSettings';
  import type { StorageSettings as StorageSettingsType } from '@slink/lib/settings/Type/StorageSettings';

  interface Props {
    settings: StorageSettingsType;
    defaultSettings?: StorageSettingsType;
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

<SettingsPane category="storage" {loading} on={{ save: onSave }}>
  {#snippet title()}
    {$t('settings.storage.title')}
  {/snippet}
  {#snippet description()}
    {$t('settings.storage.description')}
  {/snippet}

  <SettingItem
    defaultValue={defaultSettings?.provider}
    currentValue={settings.provider}
    reset={(value) => {
      settings.provider = value;
    }}
  >
    {#snippet label()}
      {$t('settings.storage.provider.label')}
    {/snippet}
    {#snippet hint()}
      {$t('settings.storage.provider.hint')}
    {/snippet}
    <Select
      class="w-full max-w-md"
      type="single"
      items={[
        { value: 'local', label: $t('settings.storage.provider.local') },
        { value: 'smb', label: $t('settings.storage.provider.smb') },
        { value: 's3', label: $t('settings.storage.provider.s3') },
      ]}
      bind:value={settings.provider}
    />
  </SettingItem>

  {#if settings.provider === 'smb'}
    <SettingItem
      defaultValue={defaultSettings?.adapter.smb.host}
      currentValue={settings.adapter.smb.host}
      reset={(value) => {
        settings.adapter.smb.host = value;
      }}
    >
      {#snippet label()}
        {$t('settings.storage.smb.server_host.label')}
      {/snippet}
      {#snippet hint()}
        {$t('settings.storage.smb.server_host.hint')}
      {/snippet}
      <Input
        name="smbHost"
        placeholder="192.168.1.100 or server.local"
        bind:value={settings.adapter.smb.host}
        size="md"
      />
    </SettingItem>

    <SettingItem
      defaultValue={defaultSettings?.adapter.smb.share}
      currentValue={settings.adapter.smb.share}
      reset={(value) => {
        settings.adapter.smb.share = value;
      }}
    >
      {#snippet label()}
        {$t('settings.storage.smb.share_name.label')}
      {/snippet}
      {#snippet hint()}
        {$t('settings.storage.smb.share_name.hint')}
      {/snippet}
      <Input
        name="smbShare"
        placeholder="uploads"
        bind:value={settings.adapter.smb.share}
        size="md"
      />
    </SettingItem>

    <SettingItem
      defaultValue={defaultSettings?.adapter.smb.workgroup}
      currentValue={settings.adapter.smb.workgroup}
      reset={(value) => {
        settings.adapter.smb.workgroup = value;
      }}
    >
      {#snippet label()}
        {$t('settings.storage.smb.workgroup.label')}
      {/snippet}
      {#snippet hint()}
        {$t('settings.storage.smb.workgroup.hint')}
      {/snippet}
      <Input
        name="smbWorkgroup"
        placeholder="WORKGROUP"
        bind:value={settings.adapter.smb.workgroup}
        size="md"
      />
    </SettingItem>

    <SettingItem
      defaultValue={defaultSettings?.adapter.smb.username}
      currentValue={settings.adapter.smb.username}
      reset={(value) => {
        settings.adapter.smb.username = value;
      }}
    >
      {#snippet label()}
        {$t('settings.storage.smb.username.label')}
      {/snippet}
      {#snippet hint()}
        {$t('settings.storage.smb.username.hint')}
      {/snippet}
      <Input
        name="smbUsername"
        bind:value={settings.adapter.smb.username}
        size="md"
      />
    </SettingItem>

    <SettingItem
      defaultValue={defaultSettings?.adapter.smb.password}
      currentValue={settings.adapter.smb.password}
      reset={(value) => {
        settings.adapter.smb.password = value;
      }}
    >
      {#snippet label()}
        {$t('settings.storage.smb.password.label')}
      {/snippet}
      {#snippet hint()}
        {$t('settings.storage.smb.password.hint')}
      {/snippet}
      <Input
        type="password"
        name="smbPassword"
        bind:value={settings.adapter.smb.password}
        size="md"
      />
    </SettingItem>
  {/if}

  {#if settings.provider === 's3'}
    <SettingItem
      defaultValue={defaultSettings?.adapter.s3.useCustomProvider}
      currentValue={settings.adapter.s3.useCustomProvider}
      reset={(value) => {
        settings.adapter.s3.useCustomProvider = value;
      }}
    >
      {#snippet label()}
        {$t('settings.storage.s3.custom_provider.label')}
      {/snippet}
      {#snippet hint()}
        {$t('settings.storage.s3.custom_provider.hint')}
      {/snippet}
      <Switch
        name="s3UseCustomProvider"
        bind:checked={settings.adapter.s3.useCustomProvider}
      />
    </SettingItem>

    {#if !settings.adapter.s3.useCustomProvider}
      <Notice variant="warning" appearance="subtle" size="sm" class="px-4">
        {$t('settings.storage.s3.pricing_notice')}
        <a
          href="https://aws.amazon.com/s3/pricing/"
          target="_blank"
          rel="noopener noreferrer"
          class="underline hover:text-amber-700 dark:hover:text-amber-300"
          >{$t('settings.storage.s3.review_pricing')}</a
        >
      </Notice>
    {/if}

    {#if settings.adapter.s3.useCustomProvider}
      <SettingItem
        defaultValue={defaultSettings?.adapter.s3.endpoint}
        currentValue={settings.adapter.s3.endpoint}
        reset={(value) => {
          settings.adapter.s3.endpoint = value;
        }}
      >
        {#snippet label()}
          {$t('settings.storage.s3.custom_endpoint.label')}
        {/snippet}
        {#snippet hint()}
          {$t('settings.storage.s3.custom_endpoint.hint')}
        {/snippet}
        <Input
          name="s3Endpoint"
          placeholder="http://localhost:9000"
          bind:value={settings.adapter.s3.endpoint}
          size="md"
        />
      </SettingItem>

      <SettingItem
        defaultValue={defaultSettings?.adapter.s3.region}
        currentValue={settings.adapter.s3.region}
        reset={(value) => {
          settings.adapter.s3.region = value;
        }}
      >
        {#snippet label()}
          {$t('settings.storage.s3.region_optional.label')}
        {/snippet}
        {#snippet hint()}
          {$t('settings.storage.s3.region_optional.hint')}
        {/snippet}
        <Input
          name="s3Region"
          placeholder="auto"
          bind:value={settings.adapter.s3.region}
          size="md"
        />
      </SettingItem>

      <SettingItem
        defaultValue={defaultSettings?.adapter.s3.forcePathStyle}
        currentValue={settings.adapter.s3.forcePathStyle}
        reset={(value) => {
          settings.adapter.s3.forcePathStyle = value;
        }}
      >
        {#snippet label()}
          {$t('settings.storage.s3.force_path_style.label')}
        {/snippet}
        {#snippet hint()}
          {$t('settings.storage.s3.force_path_style.hint')}
        {/snippet}
        <Switch
          name="s3ForcePathStyle"
          bind:checked={settings.adapter.s3.forcePathStyle}
        />
      </SettingItem>
    {:else}
      <SettingItem
        defaultValue={defaultSettings?.adapter.s3.region}
        currentValue={settings.adapter.s3.region}
        reset={(value) => {
          settings.adapter.s3.region = value;
        }}
      >
        {#snippet label()}
          {$t('settings.storage.s3.region.label')}
        {/snippet}
        {#snippet hint()}
          {$t('settings.storage.s3.region.hint')}
          <a
            href="https://docs.aws.amazon.com/general/latest/gr/s3.html"
            target="_blank"
            rel="noopener noreferrer"
            class="text-blue-600 dark:text-blue-400 hover:underline"
          >
            {$t('settings.storage.s3.region.view_regions')}
          </a>
        {/snippet}
        <Input
          name="s3Region"
          placeholder="us-east-1"
          bind:value={settings.adapter.s3.region}
          size="md"
        />
      </SettingItem>
    {/if}

    <SettingItem
      defaultValue={defaultSettings?.adapter.s3.bucket}
      currentValue={settings.adapter.s3.bucket}
      reset={(value) => {
        settings.adapter.s3.bucket = value;
      }}
    >
      {#snippet label()}
        {$t('settings.storage.s3.bucket_name.label')}
      {/snippet}
      {#snippet hint()}
        {$t('settings.storage.s3.bucket_name.hint')}
        <a
          href="https://docs.aws.amazon.com/AmazonS3/latest/userguide/UsingBucket.html#general-purpose-buckets-overview"
          target="_blank"
          rel="noopener noreferrer"
          class="text-blue-600 dark:text-blue-400 hover:underline"
        >
          {$t('settings.storage.s3.bucket_name.learn')}
        </a>
      {/snippet}
      <Input
        name="s3Bucket"
        placeholder="my-slink-bucket"
        bind:value={settings.adapter.s3.bucket}
        size="md"
      />
    </SettingItem>

    <SettingItem
      defaultValue={defaultSettings?.adapter.s3.key}
      currentValue={settings.adapter.s3.key}
      reset={(value) => {
        settings.adapter.s3.key = value;
      }}
    >
      {#snippet label()}
        {$t('settings.storage.s3.access_key_id.label')}
      {/snippet}
      {#snippet hint()}
        {$t('settings.storage.s3.access_key_id.hint')}
        <a
          href="https://docs.aws.amazon.com/IAM/latest/UserGuide/access-key-self-managed.html#Using_CreateAccessKey"
          target="_blank"
          rel="noopener noreferrer"
          class="text-blue-600 dark:text-blue-400 hover:underline"
        >
          {$t('settings.storage.s3.access_key_id.how_to')}
        </a>
      {/snippet}
      <Input
        name="s3AccessKey"
        bind:value={settings.adapter.s3.key}
        size="md"
      />
    </SettingItem>

    <SettingItem
      defaultValue={defaultSettings?.adapter.s3.secret}
      currentValue={settings.adapter.s3.secret}
      reset={(value) => {
        settings.adapter.s3.secret = value;
      }}
    >
      {#snippet label()}
        {$t('settings.storage.s3.secret_access_key.label')}
      {/snippet}
      {#snippet hint()}
        {$t('settings.storage.s3.secret_access_key.hint')}
      {/snippet}
      <Input
        type="password"
        name="s3SecretKey"
        bind:value={settings.adapter.s3.secret}
        size="md"
      />
    </SettingItem>
  {/if}
</SettingsPane>
