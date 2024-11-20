<script lang="ts">
  import type {
    SettingCategory,
    SettingCategoryData,
  } from '@slink/lib/settings/Type/GlobalSettings';

  import Icon from '@iconify/svelte';

  import { ApiClient } from '@slink/api/Client';
  import { ReactiveState } from '@slink/api/ReactiveState';
  import type { EmptyResponse } from '@slink/api/Response';

  import {
    SettingItem,
    SettingsPane,
  } from '@slink/components/Feature/Settings';
  import {
    Dropdown,
    DropdownItem,
    FileSizeInput,
    Input,
    Multiselect,
    MultiselectItem,
    NumberInput,
    Toggle,
  } from '@slink/components/UI/Form';

  import type { PageServerData } from './$types';

  export let data: PageServerData;

  const { run: saveSettings, isLoading } = ReactiveState<EmptyResponse>(
    (category: SettingCategory, data: SettingCategoryData) => {
      return ApiClient.setting.updateSettings(category, data);
    },
    { debounce: 300, minExecutionTime: 500 }
  );

  let categoryBeingSaved: SettingCategory | null = null;

  const handleSettingsSectionSave = async ({
    detail: { category },
  }: CustomEvent<{ category: SettingCategory }>) => {
    const { [category]: data } = settings;
    categoryBeingSaved = category;

    await saveSettings(category, data);
  };

  $: settings = data?.settings;
  $: defaultSettings = data?.defaultSettings;
</script>

<svelte:head>
  <title>Settings | Slink</title>
</svelte:head>

<div class="h-full w-full max-w-7xl px-6 py-4">
  <div class="flex flex-col gap-2">
    <SettingsPane
      category="user"
      loading={$isLoading && categoryBeingSaved === 'user'}
      on:save={handleSettingsSectionSave}
    >
      <svelte:fragment slot="title">User</svelte:fragment>
      <svelte:fragment slot="description"
        >Modify how users interact with the application</svelte:fragment
      >

      <SettingItem
        defaultValue={defaultSettings.user?.approvalRequired}
        reset={(value) => {
          settings.user.approvalRequired = value;
        }}
      >
        <svelte:fragment slot="label">User Approval</svelte:fragment>
        <svelte:fragment slot="hint"
          >Toggle whether users need to be approved before they can access the
          application</svelte:fragment
        >
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
        <svelte:fragment slot="label">Unauthenticated Access</svelte:fragment>
        <svelte:fragment slot="hint"
          >Toggle whether users can access the application without being
          authenticated</svelte:fragment
        >
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
        <svelte:fragment slot="label">Password Length</svelte:fragment>
        <svelte:fragment slot="hint"
          >Set the minimum length of a user's password</svelte:fragment
        >
        <NumberInput
          name="passwordLength"
          min={3}
          bind:value={settings.user.password.minLength}
        />
      </SettingItem>
      <SettingItem
        defaultValue={defaultSettings.user?.password.requirements}
        reset={(value) => {
          settings.user.password.requirements = value;
        }}
      >
        <svelte:fragment slot="label">Password Complexity</svelte:fragment>
        <svelte:fragment slot="hint"
          >Select the required character types for a user's password</svelte:fragment
        >
        <Multiselect
          type="bitmask"
          name="passwordRequirements"
          bind:value={settings.user.password.requirements}
        >
          <MultiselectItem key="1">
            <Icon icon="ph:number-nine-thin" />
            Numbers
          </MultiselectItem>
          <MultiselectItem key="2">
            <Icon icon="material-symbols-light:lowercase-rounded" />
            Lowercase Letters
          </MultiselectItem>
          <MultiselectItem key="4">
            <Icon icon="material-symbols-light:uppercase-rounded" />
            Uppercase Letters
          </MultiselectItem>
          <MultiselectItem key="8">
            <Icon icon="material-symbols-light:asterisk-rounded" />
            Special Characters
          </MultiselectItem>
        </Multiselect>
      </SettingItem>
    </SettingsPane>
    <SettingsPane
      category="image"
      loading={$isLoading && categoryBeingSaved === 'image'}
      on:save={handleSettingsSectionSave}
    >
      <svelte:fragment slot="title">Image</svelte:fragment>
      <svelte:fragment slot="description"
        >Adjust image-related preferences</svelte:fragment
      >

      <SettingItem
        defaultValue={defaultSettings.image?.maxSize}
        reset={(value) => {
          settings.image.maxSize = value;
        }}
      >
        <svelte:fragment slot="label">Maximum Image Size</svelte:fragment>
        <svelte:fragment slot="hint"
          >Set the maximum size of an image that can be uploaded</svelte:fragment
        >
        <FileSizeInput
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
        <svelte:fragment slot="label">Strip EXIF Data</svelte:fragment>
        <svelte:fragment slot="hint"
          >Toggle whether EXIF data should be stripped from uploaded images</svelte:fragment
        >
        <Toggle
          name="imageStripExifMetadata"
          bind:checked={settings.image.stripExifMetadata}
        />
      </SettingItem>
    </SettingsPane>
    <SettingsPane
      category="storage"
      loading={$isLoading && categoryBeingSaved === 'storage'}
      on:save={handleSettingsSectionSave}
    >
      <svelte:fragment slot="title">Storage</svelte:fragment>
      <svelte:fragment slot="description"
        >Configure your preferred way of storing data</svelte:fragment
      >

      <SettingItem
        defaultValue={defaultSettings.storage?.provider}
        reset={(value) => {
          settings.storage.provider = value;
        }}
      >
        <svelte:fragment slot="label">Storage Type</svelte:fragment>
        <svelte:fragment slot="hint"
          >Select where you want to store your data</svelte:fragment
        >
        <Dropdown
          name="storageProvider"
          variant="form"
          rounded="lg"
          size="md"
          bind:selected={settings.storage.provider}
        >
          <DropdownItem key="local">Local</DropdownItem>
          <DropdownItem key="smb">Samba (SMB)</DropdownItem>
        </Dropdown>
      </SettingItem>
      {#if settings.storage.provider === 'smb'}
        <SettingItem
          defaultValue={defaultSettings.storage?.adapter.smb.host}
          reset={(value) => {
            settings.storage.adapter.smb.host = value;
          }}
        >
          <svelte:fragment slot="label">SMB Host</svelte:fragment>
          <svelte:fragment slot="hint"
            >Enter the IP address or hostname of your SMB server</svelte:fragment
          >
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
          <svelte:fragment slot="label">SMB Share</svelte:fragment>
          <svelte:fragment slot="hint"
            >Enter the name of the share on your SMB server</svelte:fragment
          >
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
          <svelte:fragment slot="label">SMB Workgroup</svelte:fragment>
          <svelte:fragment slot="hint"
            >Enter the workgroup of your SMB server</svelte:fragment
          >
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
          <svelte:fragment slot="label">SMB Username</svelte:fragment>
          <svelte:fragment slot="hint"
            >Enter the username to authenticate with your SMB server</svelte:fragment
          >
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
          <svelte:fragment slot="label">SMB Password</svelte:fragment>
          <svelte:fragment slot="hint"
            >Enter the password to authenticate with your SMB server</svelte:fragment
          >
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
