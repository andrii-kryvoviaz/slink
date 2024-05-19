<script lang="ts">
  import type { User } from '@slink/lib/auth/Type/User';

  import Icon from '@iconify/svelte';

  import { Dropdown } from '@slink/components/Common';
  import { Badge } from '@slink/components/Content';
  import { UserAvatar } from '@slink/components/User';
  import UserStatus from '@slink/components/User/UserStatus.svelte';

  export let user: Partial<User> = {};
  export let loggedInUser: Partial<User> = {};

  $: isAdmin = user.roles?.includes('ROLE_ADMIN');
  $: isCurrentUser = user.id === loggedInUser.id;
</script>

<div
  class="flex items-center justify-between rounded-xl border border-header/50 p-4"
>
  <div class="flex w-full items-center space-x-4">
    <UserAvatar {user} size="lg" />
    <div class="flex w-full flex-col">
      <div class="flex w-full justify-between">
        <span class="text-lg font-semibold">{user.displayName}</span>

        <Dropdown>
          <div class="flex flex-col gap-2 p-3">
            <button class="tooltip-item">
              <Icon icon="ic:round-edit" />
              <span>Rename</span>
            </button>
            <button class="tooltip-item">
              <Icon icon="bi:pin-angle-fill" />
              <span>Pin</span>
            </button>
            <hr class="border-gray-500/70" />
            <button class="tooltip-item danger">
              <Icon icon="ic:round-delete" />
              <span>Delete</span>
            </button>
          </div>
        </Dropdown>
      </div>

      <span class="text-sm text-gray-500">{user.email}</span>
      <div class="badges mt-2 space-x-1">
        {#if isAdmin}
          <Badge variant="primary" size="sm" outline="true">Admin</Badge>
        {/if}
        {#if isCurrentUser}
          <Badge variant="warning" size="sm" outline="true">You</Badge>
        {:else}
          <UserStatus status={user.status} />
        {/if}
      </div>
    </div>
  </div>
</div>

<style>
  .active {
    @apply bg-stone-950 text-gray-50;
  }

  .active .shadow-blur {
    @apply from-stone-950;
  }

  .tooltip-item {
    @apply flex cursor-pointer items-center gap-2 rounded-md p-1 px-3;
    @apply text-gray-50;
  }

  .tooltip-item[disabled] {
    @apply cursor-not-allowed text-gray-500;
  }

  .tooltip-item:hover {
    @apply bg-gray-500 text-gray-50;
  }

  .danger:hover {
    @apply bg-red-500 text-gray-50;
  }

  .tooltip-item[disabled]:hover {
    @apply bg-inherit text-gray-500;
  }
</style>
