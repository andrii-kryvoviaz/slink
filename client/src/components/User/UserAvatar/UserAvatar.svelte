<script lang="ts">
  import type { User } from '@slink/lib/auth/Type/User';

  import { className } from '@slink/utils/ui/className';

  import { UserAvatarTheme } from '@slink/components/User';
  import type { UserAvatarProps } from '@slink/components/User';

  interface $$Props extends UserAvatarProps {
    class?: string;
    user?: Partial<User>;
  }

  export let variant: $$Props['variant'] = 'default';

  export let size: $$Props['size'] = 'md';

  export let user: $$Props['user'] = {};

  $: classes = `${UserAvatarTheme({
    variant,
    size,
  })} flex items-center justify-center ${$$props.class}`;

  const colors = [
    '#C70039',
    '#900C3F',
    '#581845',
    '#1C2833',
    '#17202A',
    '#BAE1FF',
    '#E6BEFF',
    '#FFBE88',
    '#FF6961',
    '#AEC6CF',
    '#B39EB5',
    '#FFB347',
    '#B19CD9',
    '#836953',
    '#779ECB',
    '#966FD6',
    '#C23B22',
  ];

  function getColorFromId(userId: string) {
    let sum = 0;
    for (let i = 0; i < userId.length; i++) {
      sum += userId.charCodeAt(i);
    }
    const colorIndex = sum % colors.length;
    return colors[colorIndex];
  }

  $: backgroundColor = getColorFromId(user?.id || '');
  $: userShortName = user?.displayName?.at(0)?.toUpperCase();
</script>

<div class={className(classes)} style={`background-color: ${backgroundColor}`}>
  {userShortName}
</div>
