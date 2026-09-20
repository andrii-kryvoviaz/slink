<script lang="ts">
  import {
    formatDateTime,
    formatRecentTime,
    minuteClock,
  } from '$lib/utils/date.svelte';

  import { notificationTime } from './NotificationTime.theme';

  interface Props {
    timestamp: number;
    class?: string;
  }

  let { timestamp, class: className }: Props = $props();

  const createdAt = $derived(new Date(timestamp * 1000));
</script>

<time
  datetime={createdAt.toISOString()}
  title={formatDateTime(createdAt)}
  class={notificationTime({ class: className })}
>
  {formatRecentTime(createdAt, minuteClock.now)}
</time>
