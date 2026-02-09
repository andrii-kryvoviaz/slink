<script lang="ts">
  import CopyableText from '@slink/feature/Text/CopyableText/CopyableText.svelte';
  import { UserAvatar } from '@slink/feature/User';

  import type { User } from '$lib/auth/Type/User';

  interface Props {
    user: User;
  }

  let { user }: Props = $props();
</script>

<div class="flex items-center space-x-3">
  <UserAvatar {user} size="md" />
  <div class="min-w-0">
    <div class="font-medium text-gray-900 dark:text-white truncate">
      {user.displayName}
    </div>
    <div class="text-sm text-gray-500 dark:text-gray-400 truncate">
      <CopyableText text={user.email} />
    </div>
  </div>
</div>
