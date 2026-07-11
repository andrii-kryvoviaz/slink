<script lang="ts">
  import { UserAvatar, type UserAvatarSize } from '@slink/feature/User';

  import { cn } from '$lib/utils/ui';

  import { avatarStackTheme } from './AvatarStack.theme';

  interface Props {
    users: { displayName: string; email: string; avatar?: string }[];
    max?: number;
    size?: UserAvatarSize;
    ringClass?: string;
    class?: string;
  }

  let {
    users,
    max = 3,
    size = 'sm',
    ringClass = 'ring-white dark:ring-gray-900',
    class: className,
  }: Props = $props();
</script>

<div class={cn(avatarStackTheme(), className)}>
  {#each users.slice(0, max) as user, index (index)}
    <UserAvatar {user} {size} class={cn('ring-2', ringClass)} />
  {/each}
</div>
