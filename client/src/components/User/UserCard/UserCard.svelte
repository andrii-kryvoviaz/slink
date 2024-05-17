<script lang="ts">
  import type { User } from '@slink/lib/auth/Type/User';

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
  <div class="flex items-center space-x-4">
    <UserAvatar {user} size="lg" />
    <div class="flex flex-col">
      <span class="text-lg font-semibold">{user.displayName}</span>
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
