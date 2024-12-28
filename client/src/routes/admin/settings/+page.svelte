<script lang="ts">
  import type { PageServerData } from './$types';
  import type { EmptyResponse } from '@slink/api/Response';
  import type {
    SettingCategory,
    SettingCategoryData,
  } from '@slink/lib/settings/Type/GlobalSettings';

  import { ApiClient } from '@slink/api/Client';
  import { ReactiveState } from '@slink/api/ReactiveState';

  import {
    SettingItem,
    SettingsPane,
  } from '@slink/components/Feature/Settings';
  import {
    FileSizeInput,
    Input,
    NumberInput,
    Select,
    Toggle,
  } from '@slink/components/UI/Form';

  interface Props {
    data: PageServerData;
  }

  let { data }: Props = $props();

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
  };

  let settings = $state(data?.settings);
  let defaultSettings = $state(data?.defaultSettings);
</script>

<svelte:head>
  <title>Settings | Slink</title>
</svelte:head>

<div class="h-full w-full max-w-7xl px-6 py-4">
  <div class="flex flex-col gap-2">
    <SettingsPane
      category="user"
      loading={$isLoading && categoryBeingSaved === 'user'}
      on={{ save: handleSettingsSectionSave }}
    >
      {#snippet title()}
        User
      {/snippet}
      {#snippet description()}
        Modify how users interact with the application
      {/snippet}

      <SettingItem
        defaultValue={defaultSettings.user?.allowRegistration}
        reset={(value) => {
          settings.user.allowRegistration = value;
        }}
      >
        {#snippet label()}
          User Signup Enabled
        {/snippet}
        {#snippet hint()}
          Toggle whether users are able to create a new account
        {/snippet}
        <Toggle
          name="allowRegistration"
          bind:checked={settings.user.allowRegistration}
        ></Toggle>
      </SettingItem>
      {#if settings.user.allowRegistration}
        <SettingItem
          defaultValue={defaultSettings.user?.approvalRequired}
          reset={(value) => {
            settings.user.approvalRequired = value;
          }}
        >
          {#snippet label()}
            User Approval
          {/snippet}
          {#snippet hint()}
            Toggle whether users need to be approved before they can access the
            application
          {/snippet}
          <Toggle
            name="approvalRequired"
            bind:checked={settings.user.approvalRequired}
          />
        </SettingItem>
        <SettingItem
          defaultValue={defaultSettings.user?.allowUnauthenticatedAccess}
          reset={(value) => {
            settings.user.allowUnauthenticatedAccess = value;
          }}
        >
          {#snippet label()}
            Unauthenticated Access
          {/snippet}
          {#snippet hint()}
            Toggle whether users can access the application without being
            authenticated
          {/snippet}
          <Toggle
            name="allowUnauthenticatedAccess"
            bind:checked={settings.user.allowUnauthenticatedAccess}
          />
        </SettingItem>
        <SettingItem
          defaultValue={defaultSettings.user?.password.minLength}
          reset={(value) => {
            settings.user.password.minLength = value;
          }}
        >
          {#snippet label()}
            Password Length
          {/snippet}
          {#snippet hint()}
            Set the minimum length of a user's password
          {/snippet}
          <NumberInput
            error={$error?.errors.password?.minLength}
            name="passwordLength"
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
            Password Complexity
          {/snippet}
          {#snippet hint()}
            Select the required character types for a user's password
          {/snippet}
          <Select
            name="passwordRequirements"
            type="bitmask"
            class="w-72"
            items={[
              { value: '1', label: 'Numbers', icon: 'ph:number-nine-thin' },
              {
                value: '2',
                label: 'Lowercase Letters',
                icon: 'material-symbols-light:lowercase-rounded',
              },
              {
                value: '4',
                label: 'Uppercase Letters',
                icon: 'material-symbols-light:uppercase-rounded',
              },
              {
                value: '8',
                label: 'Special Characters',
                icon: 'material-symbols-light:asterisk-rounded',
              },
            ]}
            bind:value={settings.user.password.requirements}
          ></Select>
        </SettingItem>
      {/if}
    </SettingsPane>
    <SettingsPane
      category="image"
      loading={$isLoading && categoryBeingSaved === 'image'}
      on={{ save: handleSettingsSectionSave }}
    >
      {#snippet title()}
        Image
      {/snippet}
      {#snippet description()}
        Adjust image-related preferences
      {/snippet}

      <SettingItem
        defaultValue={defaultSettings.image?.maxSize}
        reset={(value) => {
          settings.image.maxSize = value;
        }}
      >
        {#snippet label()}
          Maximum Image Size
        {/snippet}
        {#snippet hint()}
          Set the maximum size of an image that can be uploaded
        {/snippet}
        <FileSizeInput
          error={$error?.errors.image?.maxSize}
          name="imageMaxSize"
          bind:value={settings.image.maxSize}
        />
      </SettingItem>
      <SettingItem
        defaultValue={defaultSettings.image?.stripExifMetadata}
        reset={(value) => {
          settings.image.stripExifMetadata = value;
        }}
      >
        {#snippet label()}
          Strip EXIF Data
        {/snippet}
        {#snippet hint()}
          Toggle whether EXIF data should be stripped from uploaded images
        {/snippet}
        <Toggle
          name="imageStripExifMetadata"
          bind:checked={settings.image.stripExifMetadata}
        />
      </SettingItem>
    </SettingsPane>
    <SettingsPane
      category="storage"
      loading={$isLoading && categoryBeingSaved === 'storage'}
      on={{ save: handleSettingsSectionSave }}
    >
      {#snippet title()}
        Storage
      {/snippet}
      {#snippet description()}
        Configure your preferred way of storing data
      {/snippet}

      <SettingItem
        defaultValue={defaultSettings.storage?.provider}
        reset={(value) => {
          settings.storage.provider = value;
        }}
      >
        {#snippet label()}
          Storage Type
        {/snippet}
        {#snippet hint()}
          Select where you want to store your data
        {/snippet}
        <Select
          name="storageProvider"
          type="single"
          items={[
            { value: 'local', label: 'Local' },
            { value: 'smb', label: 'Samba (SMB)' },
          ]}
          bind:value={settings.storage.provider}
        ></Select>
      </SettingItem>
      {#if settings.storage.provider === 'smb'}
        <SettingItem
          defaultValue={defaultSettings.storage?.adapter.smb.host}
          reset={(value) => {
            settings.storage.adapter.smb.host = value;
          }}
        >
          {#snippet label()}
            SMB Host
          {/snippet}
          {#snippet hint()}
            Enter the IP address or hostname of your SMB server
          {/snippet}
          <Input
            name="smbHost"
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
            SMB Share
          {/snippet}
          {#snippet hint()}
            Enter the name of the share on your SMB server
          {/snippet}
          <Input
            name="smbShare"
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
            SMB Workgroup
          {/snippet}
          {#snippet hint()}
            Enter the workgroup of your SMB server
          {/snippet}
          <Input
            name="smbWorkgroup"
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
            SMB Username
          {/snippet}
          {#snippet hint()}
            Enter the username to authenticate with your SMB server
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
            SMB Password
          {/snippet}
          {#snippet hint()}
            Enter the password to authenticate with your SMB server
          {/snippet}
          <Input
            type="password"
            name="smbPassword"
            bind:value={settings.storage.adapter.smb.password}
          />
        </SettingItem>
      {/if}
    </SettingsPane>
  </div>
</div>
