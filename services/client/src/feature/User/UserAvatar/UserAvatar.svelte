<script lang="ts">
  import * as Avatar from '@slink/ui/components/avatar';

  import { cn } from '$lib/utils/ui';

  import {
    type UserAvatarSize,
    userAvatarFallbackVariants,
    userAvatarVariants,
  } from './UserAvatar.theme';

  let {
    user,
    size = 'md',
    class: className = '',
  }: {
    user: { displayName: string; email: string; avatar?: string };
    size?: UserAvatarSize;
    class?: string;
  } = $props();

  function getInitials(name?: string): string {
    if (!name) return 'G';

    return name
      .split(' ')
      .map((n) => n[0])
      .join('')
      .toUpperCase();
  }
</script>

<Avatar.Root class={cn(userAvatarVariants({ size }), className)}>
  {#if user.avatar}
    <Avatar.Image src={user.avatar} alt={user.displayName} />
  {/if}
  <Avatar.Fallback class={cn(userAvatarFallbackVariants({ size }))}>
    {getInitials(user.displayName)}
  </Avatar.Fallback>
</Avatar.Root>
