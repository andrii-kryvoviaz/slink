<script lang="ts">
  import type { User } from '@slink/lib/auth/Type/User';

  import { className } from '@slink/utils/ui/className';

  import { UserAvatarTheme } from '@slink/components/User';
  import type { UserAvatarProps } from '@slink/components/User';

  // ToDo: Implement proper user avatar

  interface $$Props extends UserAvatarProps {
    class?: string;
    user?: User;
  }

  export let variant: $$Props['variant'] = 'default';

  export let size: $$Props['size'] = 'md';

  export let user: $$Props['user'] = null;

  $: classes = `${UserAvatarTheme({
    variant,
    size,
  })} flex items-center justify-center ${$$props.class}`;
</script>

<img
  class={className(classes)}
  src={`https://i.pravatar.cc/300?u=${user?.id}`}
  alt="avatar"
/>
