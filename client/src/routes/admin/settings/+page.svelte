<script lang="ts">
  import Icon from '@iconify/svelte';

  import { Dropdown, DropdownItem } from '@slink/components/Form';
  import {
    Input,
    Multiselect,
    MultiselectItem,
    Toggle,
  } from '@slink/components/Form';
  import { SettingItem, SettingsPane } from '@slink/components/Layout';
</script>

<div class="h-full w-full max-w-7xl px-6 py-4">
  <div class="flex flex-col gap-2">
    <SettingsPane>
      <svelte:fragment slot="title">User</svelte:fragment>
      <svelte:fragment slot="description"
        >Modify how users interact with the application</svelte:fragment
      >

      <SettingItem defaultValue="Enabled">
        <svelte:fragment slot="label">User Approval</svelte:fragment>
        <svelte:fragment slot="hint"
          >Toggle whether users need to be approved before they can access the
          application</svelte:fragment
        >
        <Toggle name="user_approval" />
      </SettingItem>
      <SettingItem defaultValue="Enabled">
        <svelte:fragment slot="label">Unauthenticated Access</svelte:fragment>
        <svelte:fragment slot="hint"
          >Toggle whether users can access the application without being
          authenticated</svelte:fragment
        >
        <Toggle name="unauthenticated_access" />
      </SettingItem>
      <SettingItem defaultValue="6">
        <svelte:fragment slot="label">Password Length</svelte:fragment>
        <svelte:fragment slot="hint"
          >Set the minimum length of a user's password</svelte:fragment
        >
        <Input type="number" name="password_length" value="6" />
      </SettingItem>
      <SettingItem defaultValue="15">
        <svelte:fragment slot="label">Password Complexity</svelte:fragment>
        <svelte:fragment slot="hint"
          >Select the required character types for a user's password</svelte:fragment
        >
        <Multiselect value={15} type="bitmask">
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
    <SettingsPane>
      <svelte:fragment slot="title">Image</svelte:fragment>
      <svelte:fragment slot="description"
        >Adjust image-related preferences</svelte:fragment
      >

      <SettingItem defaultValue="50MB">
        <svelte:fragment slot="label">Maximum Image Size (MB)</svelte:fragment>
        <svelte:fragment slot="hint"
          >Set the maximum size of an image that can be uploaded</svelte:fragment
        >
        <Input type="number" name="max_image_size" value="50" />
      </SettingItem>
      <SettingItem defaultValue="Enabled">
        <svelte:fragment slot="label">Strip EXIF Data</svelte:fragment>
        <svelte:fragment slot="hint"
          >Toggle whether EXIF data should be stripped from uploaded images</svelte:fragment
        >
        <Toggle name="strip_exif_data" />
      </SettingItem>
    </SettingsPane>
    <SettingsPane>
      <svelte:fragment slot="title">Storage</svelte:fragment>
      <svelte:fragment slot="description"
        >Configure your preferred way of storing data</svelte:fragment
      >

      <SettingItem defaultValue="Local">
        <svelte:fragment slot="label">Storage Type</svelte:fragment>
        <svelte:fragment slot="hint"
          >Select where you want to store your data</svelte:fragment
        >
        <Dropdown name="storage_type">
          <DropdownItem key="local">Local</DropdownItem>
          <DropdownItem key="smb">Samba (SMB)</DropdownItem>
        </Dropdown>
      </SettingItem>
    </SettingsPane>
  </div>
</div>
