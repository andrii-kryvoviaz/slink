<script lang="ts">
  import type { Snippet } from 'svelte';

  import { notificationActorName } from './NotificationActorName.theme';

  interface Props {
    class?: string;
    children: Snippet;
  }

  let { class: className, children }: Props = $props();
</script>

<span class={notificationActorName({ class: className })}>
  {@render children()}
</span>
