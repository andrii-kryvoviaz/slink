<script lang="ts">
  import { Badge } from '@slink/feature/Text';
  import { UserStatus } from '@slink/feature/User';

  import type { User } from '$lib/auth/Type/User';
  import Icon from '@iconify/svelte';

  interface Props {
    user: User;
    isCurrentUser: boolean;
  }

  let { user, isCurrentUser }: Props = $props();
</script>

{#if isCurrentUser}
  <Badge size="xs" outline variant="warning">
    <Icon icon="heroicons:user" class="w-3 h-3 mr-1" />
    You
  </Badge>
{:else}
  <UserStatus status={user.status} size="xs" />
{/if}
