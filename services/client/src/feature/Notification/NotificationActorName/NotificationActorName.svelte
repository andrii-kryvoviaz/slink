<script lang="ts">
  import type { Snippet } from 'svelte';

  import { notificationActorName } from './NotificationActorName.theme';

  interface Props {
    read: boolean;
    class?: string;
    children: Snippet;
  }

  let { read, class: className, children }: Props = $props();
</script>

<span class={notificationActorName({ read, class: className })}>
  {@render children()}
</span>
