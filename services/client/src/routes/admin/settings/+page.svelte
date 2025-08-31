<script lang="ts">
  import { SettingItem, SettingsPane } from '@slink/feature/Settings';
  import { Notice } from '@slink/feature/Text';
  import { Button } from '@slink/legacy/UI/Action';
  import {
    FileSizeInput,
    Input,
    NumberInput,
    Select,
  } from '@slink/legacy/UI/Form';
  import { Switch } from '@slink/ui/components/switch';

  import { ApiClient } from '@slink/api/Client';
  import { ReactiveState } from '@slink/api/ReactiveState';
  import type { EmptyResponse } from '@slink/api/Response';

  import type {
    SettingCategory,
    SettingCategoryData,
  } from '@slink/lib/settings/Type/GlobalSettings';
  import { useGlobalSettings } from '@slink/lib/state/GlobalSettings.svelte';

  import type { PageServerData } from './$types';

  interface Props {
    data: PageServerData;
  }

  let { data }: Props = $props();

  const globalSettingsManager = useGlobalSettings();

  const {
    run: saveSettings,
    isLoading,
    error,
  } = ReactiveState<EmptyResponse>(
    (category: SettingCategory, data: SettingCategoryData) => {
      return ApiClient.setting.updateSettings(category, data);
    },
    { debounce: 300, minExecutionTime: 500 },
  );

  let categoryBeingSaved: SettingCategory | null = $state(null);

  const handleSettingsSectionSave = async ({
    category,
  }: {
    category: SettingCategory;
  }) => {
    const { [category]: data } = settings;
    categoryBeingSaved = category;

    await saveSettings(category, data);

    if (!$error) {
      globalSettingsManager.updateCategory(category, data);
    }
  };

  let settings = $derived(globalSettingsManager.settings);
  let defaultSettings = $state(data?.defaultSettings);
</script>

<svelte:head>
  <title>Settings | Slink</title>
</svelte:head>

{#if globalSettingsManager.isInitialized}
  <div class="flex flex-col w-full max-w-4xl px-8 py-8 space-y-12">
    <div class="space-y-3">
      <h1
        class="text-3xl font-light text-gray-900 dark:text-white tracking-tight"
      >
        Settings
      </h1>
      <p
        class="text-gray-600 dark:text-gray-400 text-base leading-relaxed max-w-2xl"
      >
        Configure your application preferences and manage system behavior
      </p>
    </div>

    <div class="space-y-16">
      <SettingsPane
        category="image"
        loading={$isLoading && categoryBeingSaved === 'image'}
        on={{ save: handleSettingsSectionSave }}
      >
        {#snippet title()}
          Image Settings
        {/snippet}
        {#snippet description()}
          Configure image upload limits and processing options
        {/snippet}

        <SettingItem
          defaultValue={defaultSettings?.image?.maxSize}
          reset={(value) => {
            settings.image.maxSize = value;
          }}
        >
          {#snippet label()}
            Maximum Image Size
          {/snippet}
          {#snippet hint()}
            Set the maximum size limit for image uploads
          {/snippet}
          <FileSizeInput
            name="imageMaxSize"
            bind:value={settings.image.maxSize}
          />
        </SettingItem>

        <SettingItem
          defaultValue={defaultSettings?.image?.stripExifMetadata}
          reset={(value) => {
            settings.image.stripExifMetadata = value;
          }}
        >
          {#snippet label()}
            Strip EXIF Metadata
          {/snippet}
          {#snippet hint()}
            Automatically remove metadata from uploaded images for privacy
          {/snippet}
          <div class="flex justify-end">
            <Switch
              name="imageStripExifMetadata"
              bind:checked={settings.image.stripExifMetadata}
            />
          </div>
        </SettingItem>

        <SettingItem
          defaultValue={defaultSettings?.image?.allowOnlyPublicImages}
          reset={(value) => {
            settings.image.allowOnlyPublicImages = value;
          }}
        >
          {#snippet label()}
            Allow Only Public Images
          {/snippet}
          {#snippet hint()}
            When enabled, all images are automatically set to public and
            visibility cannot be changed
          {/snippet}
          <div class="flex justify-end">
            <Switch
              name="imageAllowOnlyPublicImages"
              bind:checked={settings.image.allowOnlyPublicImages}
            />
          </div>
        </SettingItem>
      </SettingsPane>

      <SettingsPane
        category="access"
        loading={$isLoading && categoryBeingSaved === 'access'}
        on={{ save: handleSettingsSectionSave }}
      >
        {#snippet title()}
          Access Control
        {/snippet}
        {#snippet description()}
          Configure guest access and upload permissions
        {/snippet}

        <SettingItem
          defaultValue={defaultSettings?.access?.allowGuestUploads}
          reset={(value) => {
            settings.access.allowGuestUploads = value;
          }}
        >
          {#snippet label()}
            Guest Upload
          {/snippet}
          {#snippet hint()}
            Allow unauthenticated users to upload images without creating an
            account. When enabled without Guest Access, users will see a success
            message but cannot browse uploaded images
          {/snippet}
          <div class="flex justify-end">
            <Switch
              name="accessAllowGuestUploads"
              bind:checked={settings.access.allowGuestUploads}
            />
          </div>
        </SettingItem>

        <SettingItem
          defaultValue={defaultSettings?.access?.allowUnauthenticatedAccess}
          reset={(value) => {
            settings.access.allowUnauthenticatedAccess = value;
          }}
        >
          {#snippet label()}
            Guest Access (View-Only)
          {/snippet}
          {#snippet hint()}
            Allow unauthenticated users to view and browse images
          {/snippet}
          <div class="flex justify-end">
            <Switch
              name="accessAllowUnauthenticatedAccess"
              bind:checked={settings.access.allowUnauthenticatedAccess}
            />
          </div>
        </SettingItem>
      </SettingsPane>

      <SettingsPane
        category="storage"
        loading={$isLoading && categoryBeingSaved === 'storage'}
        on={{ save: handleSettingsSectionSave }}
      >
        {#snippet title()}
          Storage Configuration
        {/snippet}
        {#snippet description()}
          Choose how and where your data is stored
        {/snippet}

        <SettingItem
          defaultValue={defaultSettings.storage?.provider}
          reset={(value) => {
            settings.storage.provider = value;
          }}
        >
          {#snippet label()}
            Storage Provider
          {/snippet}
          {#snippet hint()}
            Select your preferred storage backend
          {/snippet}
          <Select
            name="storageProvider"
            type="single"
            items={[
              { value: 'local', label: 'Local Storage' },
              { value: 'smb', label: 'Network Storage (SMB)' },
              { value: 's3', label: 'Amazon S3' },
            ]}
            bind:value={settings.storage.provider}
          />
        </SettingItem>

        {#if settings.storage.provider === 'smb'}
          <div class="space-y-6">
            <SettingItem
              defaultValue={defaultSettings.storage?.adapter.smb.host}
              reset={(value) => {
                settings.storage.adapter.smb.host = value;
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
                bind:value={settings.storage.adapter.smb.host}
              />
            </SettingItem>

            <SettingItem
              defaultValue={defaultSettings.storage?.adapter.smb.share}
              reset={(value) => {
                settings.storage.adapter.smb.share = value;
              }}
            >
              {#snippet label()}
                Share Name
              {/snippet}
              {#snippet hint()}
                The name of the shared folder on your SMB server
              {/snippet}
              <Input
                name="smbShare"
                placeholder="uploads"
                bind:value={settings.storage.adapter.smb.share}
              />
            </SettingItem>

            <SettingItem
              defaultValue={defaultSettings.storage?.adapter.smb.workgroup}
              reset={(value) => {
                settings.storage.adapter.smb.workgroup = value;
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
                bind:value={settings.storage.adapter.smb.workgroup}
              />
            </SettingItem>

            <SettingItem
              defaultValue={defaultSettings.storage?.adapter.smb.username}
              reset={(value) => {
                settings.storage.adapter.smb.username = value;
              }}
            >
              {#snippet label()}
                Username
              {/snippet}
              {#snippet hint()}
                Authentication username for SMB server
              {/snippet}
              <Input
                name="smbUsername"
                bind:value={settings.storage.adapter.smb.username}
              />
            </SettingItem>

            <SettingItem
              defaultValue={defaultSettings.storage?.adapter.smb.password}
              reset={(value) => {
                settings.storage.adapter.smb.password = value;
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
                bind:value={settings.storage.adapter.smb.password}
              />
            </SettingItem>
          </div>
        {/if}

        {#if settings.storage.provider === 's3'}
          <div class="space-y-6">
            <Notice size="sm" variant="warning">
              <strong>Billing Notice:</strong>
              Amazon S3 usage may incur charges. Review
              <a
                href="https://aws.amazon.com/s3/pricing/"
                target="_blank"
                rel="noopener noreferrer"
                class="underline font-medium hover:text-amber-700 dark:hover:text-amber-300"
              >
                S3 pricing details
              </a>
              before proceeding.
            </Notice>

            <SettingItem
              defaultValue={defaultSettings.storage?.adapter.s3.region}
              reset={(value) => {
                settings.storage.adapter.s3.region = value;
              }}
            >
              {#snippet label()}
                AWS Region
              {/snippet}
              {#snippet hint()}
                The AWS region where your S3 bucket is located (e.g., us-east-1)
              {/snippet}
              <Input
                name="s3Region"
                placeholder="us-east-1"
                bind:value={settings.storage.adapter.s3.region}
              />
            </SettingItem>

            <SettingItem
              defaultValue={defaultSettings.storage?.adapter.s3.bucket}
              reset={(value) => {
                settings.storage.adapter.s3.bucket = value;
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
                bind:value={settings.storage.adapter.s3.bucket}
              />
            </SettingItem>

            <SettingItem
              defaultValue={defaultSettings.storage?.adapter.s3.key}
              reset={(value) => {
                settings.storage.adapter.s3.key = value;
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
                bind:value={settings.storage.adapter.s3.key}
              />
            </SettingItem>

            <SettingItem
              defaultValue={defaultSettings.storage?.adapter.s3.secret}
              reset={(value) => {
                settings.storage.adapter.s3.secret = value;
              }}
            >
              {#snippet label()}
                Secret Access Key
              {/snippet}
              {#snippet hint()}
                Your AWS Secret Access Key (kept secure and encrypted)
              {/snippet}
              <Input
                type="password"
                name="s3SecretKey"
                bind:value={settings.storage.adapter.s3.secret}
              />
            </SettingItem>
          </div>
        {/if}
      </SettingsPane>

      <SettingsPane
        category="user"
        loading={$isLoading && categoryBeingSaved === 'user'}
        on={{ save: handleSettingsSectionSave }}
      >
        {#snippet title()}
          User Management
        {/snippet}
        {#snippet description()}
          Control user registration, authentication, and security requirements
        {/snippet}

        <SettingItem
          defaultValue={defaultSettings.user?.allowRegistration}
          reset={(value) => {
            settings.user.allowRegistration = value;
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
              bind:checked={settings.user.allowRegistration}
            />
          </div>
        </SettingItem>

        {#if settings.user.allowRegistration}
          <div class="space-y-6">
            <SettingItem
              defaultValue={defaultSettings.user?.approvalRequired}
              reset={(value) => {
                settings.user.approvalRequired = value;
              }}
            >
              {#snippet label()}
                Require Admin Approval
              {/snippet}
              {#snippet hint()}
                New users must be approved by an administrator before accessing
                the application
              {/snippet}
              <div class="flex justify-end">
                <Switch
                  name="approvalRequired"
                  bind:checked={settings.user.approvalRequired}
                />
              </div>
            </SettingItem>

            <SettingItem
              defaultValue={defaultSettings.user?.password.minLength}
              reset={(value) => {
                settings.user.password.minLength = value;
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
                bind:value={settings.user.password.minLength}
              />
            </SettingItem>

            <SettingItem
              defaultValue={defaultSettings.user?.password.requirements}
              reset={(value) => {
                settings.user.password.requirements = value;
              }}
            >
              {#snippet label()}
                Password Requirements
              {/snippet}
              {#snippet hint()}
                Character types required in user passwords for enhanced security
              {/snippet}
              <Select
                name="passwordRequirements"
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
                bind:value={settings.user.password.requirements}
              />
            </SettingItem>
          </div>
        {/if}
      </SettingsPane>
    </div>
  </div>
{/if}
