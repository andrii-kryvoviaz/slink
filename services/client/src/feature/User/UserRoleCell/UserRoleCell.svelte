<script lang="ts">
  import type { User } from '$lib/auth/Type/User';

  import UserRoleBadge from '../UserRoleBadge/UserRoleBadge.svelte';

  interface Props {
    user: User;
  }

  let { user }: Props = $props();
</script>

<UserRoleBadge roles={user.roles} size="xs" />
