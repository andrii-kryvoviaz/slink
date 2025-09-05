<script lang="ts">
  import type { UserAvatarProps } from '@slink/feature/User';
  import { UserAvatarTheme } from '@slink/feature/User';

  import type { User } from '@slink/lib/auth/Type/User';

  import { className } from '@slink/utils/ui/className';

  interface Props extends UserAvatarProps {
    class?: string;
    user?: Partial<User>;
  }

  let { variant = 'default', size = 'md', user, ...props }: Props = $props();
  let classes = $derived(
    className(`${UserAvatarTheme({ variant, size })} ${props.class}`),
  );

  const colors = [
    '#6366f1',
    '#8b5cf6',
    '#ec4899',
    '#ef4444',
    '#f97316',
    '#eab308',
    '#22c55e',
    '#06b6d4',
    '#3b82f6',
    '#a855f7',
    '#f59e0b',
    '#10b981',
    '#0ea5e9',
    '#8b5a2b',
    '#6b7280',
    '#dc2626',
    '#7c3aed',
    '#059669',
    '#0284c7',
    '#ca8a04',
    '#be123c',
    '#9333ea',
    '#0d9488',
    '#c2410c',
  ];

  function getColorFromId(userId: string) {
    let sum = 0;
    for (let i = 0; i < userId.length; i++) {
      sum += userId.charCodeAt(i);
    }
    const colorIndex = sum % colors.length;
    return colors[colorIndex];
  }

  let backgroundColor = $derived(getColorFromId(user?.id || ''));
  let userShortName = $derived(user?.displayName?.at(0)?.toUpperCase());
</script>

<div class={classes} style={`background-color: ${backgroundColor}`}>
  {userShortName}
</div>
