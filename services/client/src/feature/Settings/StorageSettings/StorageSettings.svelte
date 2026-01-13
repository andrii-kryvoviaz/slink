<script lang="ts">
  import { SettingItem, SettingsPane } from '@slink/feature/Settings';
  import { Notice } from '@slink/feature/Text';
  import { Select } from '@slink/ui/components';
  import { Button } from '@slink/ui/components/button';
  import { Input } from '@slink/ui/components/input';
  import { Switch } from '@slink/ui/components/switch';
  import { toast } from 'svelte-sonner';

  import Icon from '@iconify/svelte';

  import { ApiClient } from '@slink/api/Client';
  import { ReactiveState } from '@slink/api/ReactiveState';
  import type { ClearCacheResponse } from '@slink/api/Resources/StorageResource';

  import type { SettingCategory } from '@slink/lib/settings/Type/GlobalSettings';
  import type { StorageSettings as StorageSettingsType } from '@slink/lib/settings/Type/StorageSettings';

  import { printErrorsAsToastMessage } from '@slink/utils/ui/printErrorsAsToastMessage';

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

  const {
    run: clearCache,
    isLoading: isClearingCache,
    error: clearCacheError,
    data: clearCacheData,
  } = ReactiveState<ClearCacheResponse>(
    () => {
      return ApiClient.storage.clearCache();
    },
    { minExecutionTime: 500 },
  );

  let showConfirmation = $state(false);

  const handleClearCache = async () => {
    await clearCache();

    if (!$clearCacheError) {
      showConfirmation = false;
    }
  };

  $effect(() => {
    if ($clearCacheData) {
      toast.success('Cache cleared successfully');
    }
  });

  $effect(() => {
    if ($clearCacheError) {
      printErrorsAsToastMessage($clearCacheError);
      showConfirmation = false;
    }
  });
</script>

<SettingsPane category="storage" {loading} on={{ save: onSave }}>
  {#snippet title()}
    Storage Configuration
  {/snippet}
  {#snippet description()}
    Choose how and where your data is stored
  {/snippet}

  <SettingItem
    defaultValue={defaultSettings?.provider}
    currentValue={settings.provider}
    reset={(value) => {
      settings.provider = value;
    }}
  >
    {#snippet label()}
      Storage Provider
    {/snippet}
    {#snippet hint()}
      Select your preferred storage backend
    {/snippet}
    <Select
      class="w-full max-w-md"
      type="single"
      items={[
        { value: 'local', label: 'Local Storage' },
        { value: 'smb', label: 'Network Storage (SMB)' },
        { value: 's3', label: 'Amazon S3' },
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
        Server Host
      {/snippet}
      {#snippet hint()}
        IP address or hostname of your SMB server
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
        Share Name
      {/snippet}
      {#snippet hint()}
        The name of the shared folder
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
        Workgroup
      {/snippet}
      {#snippet hint()}
        SMB workgroup name (optional)
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
        Username
      {/snippet}
      {#snippet hint()}
        Authentication username
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
        Password
      {/snippet}
      {#snippet hint()}
        Authentication password for SMB server
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
        Custom S3 Provider
      {/snippet}
      {#snippet hint()}
        Enable for S3-compatible services like MinIO, Cloudflare R2, or Wasabi.
        This unlocks custom endpoint and path-style options.
      {/snippet}
      <Switch
        name="s3UseCustomProvider"
        bind:checked={settings.adapter.s3.useCustomProvider}
      />
    </SettingItem>

    {#if !settings.adapter.s3.useCustomProvider}
      <Notice variant="warning" appearance="subtle" size="sm" class="px-4">
        Amazon S3 usage may incur charges.
        <a
          href="https://aws.amazon.com/s3/pricing/"
          target="_blank"
          rel="noopener noreferrer"
          class="underline hover:text-amber-700 dark:hover:text-amber-300"
          >Review pricing</a
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
          Custom Endpoint
        {/snippet}
        {#snippet hint()}
          Custom S3 endpoint URL for your provider
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
          Region (Optional)
        {/snippet}
        {#snippet hint()}
          Most S3-compatible providers don't require a region. Leave empty for
          Cloudflare R2 or providers that auto-detect. Use "auto" or a specific
          region if required by your provider.
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
          Force Path-Style URLs
        {/snippet}
        {#snippet hint()}
          Enable if your provider requires path-style buckets
          (http://host/bucket/key). Common for MinIO and self-hosted solutions.
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
          Region
        {/snippet}
        {#snippet hint()}
          AWS region where your bucket is located (e.g., us-east-1).
          <a
            href="https://docs.aws.amazon.com/general/latest/gr/s3.html"
            target="_blank"
            rel="noopener noreferrer"
            class="text-blue-600 dark:text-blue-400 hover:underline"
          >
            View available regions
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
        Bucket Name
      {/snippet}
      {#snippet hint()}
        Your S3 bucket name.
        <a
          href="https://docs.aws.amazon.com/AmazonS3/latest/userguide/UsingBucket.html#general-purpose-buckets-overview"
          target="_blank"
          rel="noopener noreferrer"
          class="text-blue-600 dark:text-blue-400 hover:underline"
        >
          Learn about bucket naming
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
        Access Key ID
      {/snippet}
      {#snippet hint()}
        Your AWS Access Key ID.
        <a
          href="https://docs.aws.amazon.com/IAM/latest/UserGuide/access-key-self-managed.html#Using_CreateAccessKey"
          target="_blank"
          rel="noopener noreferrer"
          class="text-blue-600 dark:text-blue-400 hover:underline"
        >
          How to create access keys
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
        Secret Access Key
      {/snippet}
      {#snippet hint()}
        Your AWS Secret Access Key
      {/snippet}
      <Input
        type="password"
        name="s3SecretKey"
        bind:value={settings.adapter.s3.secret}
        size="md"
      />
    </SettingItem>
  {/if}

  <div>
    <Notice size="sm" appearance="subtle" variant="info">
      <strong>Cache Management:</strong>
      Use the "Clear Cache" button below to remove all cached image transformations.
      Cached files will be regenerated on next request.
    </Notice>
  </div>

  {#snippet actions()}
    {#if showConfirmation}
      <span class="text-sm text-gray-600 dark:text-gray-400">
        Clear all cached images?
      </span>
      <Button
        variant="ghost"
        size="sm"
        rounded="full"
        onclick={() => (showConfirmation = false)}
        disabled={$isClearingCache}
      >
        Cancel
      </Button>
    {/if}
    <Button
      variant="destructive"
      size="sm"
      rounded="full"
      onclick={showConfirmation
        ? handleClearCache
        : () => (showConfirmation = true)}
      disabled={loading || $isClearingCache}
    >
      {#if $isClearingCache}
        <Icon
          icon="ph:circle-notch-duotone"
          class="w-4 h-4 mr-2 animate-spin"
        />
        Clearing...
      {:else if showConfirmation}
        <Icon icon="ph:check-duotone" class="w-4 h-4 mr-2" />
        Confirm
      {:else}
        <Icon icon="ph:trash-duotone" class="w-4 h-4 mr-2" />
        Clear Cache
      {/if}
    </Button>
  {/snippet}
</SettingsPane>
