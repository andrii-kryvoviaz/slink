<script lang="ts">
  import { className } from '@slink/utils/ui/className';

  import { UserAvatarTheme } from '@slink/components/User/UserAvatar/UserAvatar.theme';
  import type { UserAvatarProps } from '@slink/components/User/UserAvatar/UserAvatar.types';

  interface $$Props extends UserAvatarProps {
    class?: string;
  }

  export let variant: $$Props['variant'] = 'default';

  export let size: $$Props['size'] = 'md';

  $: classes = `${UserAvatarTheme({
    variant,
    size,
  })} ${$$props.class}`;
</script>

<img
  class={className(classes)}
  src="https://images.unsplash.com/photo-1570295999919-56ceb5ecca61?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=facearea&facepad=4&w=880&h=880&q=100"
  alt="avatar"
/>
